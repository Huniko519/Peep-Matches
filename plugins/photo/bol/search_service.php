<?php

class PHOTO_BOL_SearchService
{
    CONST SEARCH_LIMIT = 10;
    CONST CONTENT_INDEXING_LIMIT = 100;
    CONST CONTENT_INDEX_FILTER_PATTERN = '/[\w-]{{$wordLen},}/u';
    
    CONST ENTITY_TYPE_ALBUM = 'photo.album';
    CONST ENTITY_TYPE_PHOTO = 'photo.photo';
    
    private static $classInstance;
    
    private $entityTypes = array();

    private $photoDao;
    private $photoCacheDao;
    private $dataDao;
    private $indexDao;
    private $entityTypeDao;

    private function __construct()
    {
        $this->photoDao = PHOTO_BOL_PhotoDao::getInstance();
        $this->photoCacheDao = PHOTO_BOL_PhotoCacheDao::getInstance();
        $this->dataDao = PHOTO_BOL_SearchDataDao::getInstance();
        $this->indexDao = PHOTO_BOL_SearchIndexDao::getInstance();
        $this->entityTypeDao = PHOTO_BOL_SearchEntityTypeDao::getInstance();
        
        $this->reloadEntityTypes();
    }

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private function reloadEntityTypes()
    {
        $this->entityTypes = array();
        
        foreach ( $this->entityTypeDao->findAll() as $entityType )
        {
            $this->entityTypes[$entityType->entityType] = (int)$entityType->id;
        }
    }
    
    public function addSearchData( $entityId, $entityType, $content )
    {
        if ( empty($entityId) || empty($entityType) || empty($content) )
        {
            return;
        }
        
        if ( !array_key_exists($entityType, $this->entityTypes) )
        {
            return;
        }
        
        $this->deleteSearchItem($entityType, $entityId);
        
        $entity = new PHOTO_BOL_SearchData();
        $entity->entityTypeId = $this->entityTypes[$entityType];
        $entity->entityId = $entityId;
        $entity->content = $content;
        
        $this->dataDao->save($entity);
    }

    public function contentIndexing()
    {
        $indexedIds = array();
        $len = $this->indexDao->getMinWordLen();
        $pattern = str_replace('{$wordLen}', $len, self::CONTENT_INDEX_FILTER_PATTERN);
        
        foreach ( $this->dataDao->getDataForIndexing(self::CONTENT_INDEXING_LIMIT) as $data )
        {
            $match = array();

            if ( preg_match_all($pattern, html_entity_decode($data->content, ENT_QUOTES, 'UTF-8'), $match) )
            {
                $index = new PHOTO_BOL_SearchIndex();
                $index->entityTypeId = (int)$data->entityTypeId;
                $index->entityId = (int)$data->entityId;
                $index->content = implode(' ', $match[0]);
                
                try
                {
                    $this->indexDao->save($index);
                }
                catch ( Exception $e ) { }
            }
            
            $indexedIds[] = $data->id;
        }
        
        $this->dataDao->deleteByIdList($indexedIds);
    }
    
    public function addSearchIndex( $entityType, $entityId, $content )
    {
        if ( empty($entityId) || empty($entityType) || empty($content) )
        {
            return;
        }
        
        if ( !array_key_exists($entityType, $this->entityTypes) )
        {
            return;
        }
        
        $len = $this->indexDao->getMinWordLen();
        $pattern = str_replace('{$wordLen}', $len, self::CONTENT_INDEX_FILTER_PATTERN);
        $match = NULL;
        
        if ( preg_match_all($pattern, html_entity_decode($content, ENT_QUOTES, 'UTF-8'), $match) )
        {
            $index = new PHOTO_BOL_SearchIndex();
            $index->entityTypeId = $this->entityTypes[$entityType];
            $index->entityId = (int)$entityId;
            $index->content = implode(' ', $match[0]);

            try
            {
                $this->indexDao->save($index);
                
                return TRUE;
            }
            catch ( Exception $e )
            {
                return FALSE;
            }
        }
    }

    public function getEntityTypeId( $entityType )
    {
        return (empty($entityType) || !isset($this->entityTypes[$entityType])) ? NULL : $this->entityTypes[$entityType];
    }

    public function addEntityType( $entityType )
    {
        if ( empty($entityType) || $this->getEntityTypeId($entityType) !== NULL )
        {
            return FALSE;
        }
        
        $entity = new PHOTO_BOL_SearchEntityType();
        $entity->entityType = $entityType;
        $this->entityTypeDao->save($entity);
        
        $this->reloadEntityTypes();
        
        return $entity;
    }
    
    public function deleteSearchItem( $entityType, $entityId )
    {
        if ( !array_key_exists($entityType, $this->entityTypes) )
        {
            return FALSE;
        }
        
        if ( !$this->indexDao->deleteIndexItem($this->entityTypes[$entityType], $entityId) )
        {
            $this->dataDao->deleteDataItem($this->entityTypes[$entityType], $entityId);
        }
        
        return TRUE;
    }
    
    public function getSearchResultListByTag( $tag, $limit = self::SEARCH_LIMIT )
    {
        $result = array();
        
        foreach ( $this->photoDao->getSearchResultListByTag($tag, $limit) as $list )
        {
            $result[$list['id']] = $list;
        }
        
        return $result;
    }
    
    public function getSearchResultAllListByTag( $tag )
    {
        return $this->photoDao->getSearchResultAllListByTag($tag);
    }
    
    public function getSearchResultListByUsername( $username, $limit = self::SEARCH_LIMIT )
    {
        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
        
        $questionValues = array($questionName => $username );
        $idList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($questionValues, 0, $limit);
        
        $resultList = $this->photoDao->getSearchResultListByUserIdList($idList, $limit);
        $displayNameList = BOL_UserService::getInstance()->getDisplayNamesForList($idList);
        
        foreach ( $resultList as $key => $list )
        {
            $resultList[$key]['label'] = $displayNameList[$list['id']];
        }
        
        return $resultList;
    }
    
    public function getSearchResultAllListByUsername( $username )
    {
        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
        
        $questionValues = array($questionName => $username );
        $limit = BOL_UserService::getInstance()->countUsersByQuestionValues($questionValues);
        
        return BOL_UserService::getInstance()->findUserIdListByQuestionValues($questionValues, 0, $limit);
    }
    
    public function getSearchResultListByDescription( $description, $limit = self::SEARCH_LIMIT )
    {
        $list = $this->photoDao->getSearchResultListByDescription($description, $limit);
        $result = array();
        
        foreach ( $list as $val )
        {
            if ( mb_strlen($val['label']) > 50 )
            {
                $i = mb_stripos($val['label'], $description);
                $val['label'] = '...' . mb_substr($val['label'], ($i - 10) < 0 ? 0 : $i - 10, 50) . '...';
            }
            
            $result[$val['id']] = $val;
        }
        
        return $result;
    }
    
    public function getSearchResultAllListByDescription( $description )
    {
        return $this->photoDao->getSearchResultAllListByDescription($description);
    }
    
    public function getSearchResult( $searchVal, $limit = self::SEARCH_LIMIT )
    {
        if ( strlen($searchVal = trim($searchVal)) === 0 )
        {
            return array();
        }
        
        if ( ($cache = $this->photoCacheDao->findCacheByKey($this->photoCacheDao->getKey($searchVal))) !== NULL )
        {
            return json_decode($cache->data, TRUE);
        }
        
        if ( preg_match('/^(?:#|@)\S+/', $searchVal) === 1 )
        {
            switch ( $searchVal[0] )
            {
                case '#':
                    $list = $this->getSearchResultListByTag($searchVal, $limit);
                    $result = array('result' => TRUE, 'type' => 'hash', 'list' => $list);
                    break;
                case '@':
                    $_result = $this->getSearchResultListByUsername(trim($searchVal, '@'), self::SEARCH_LIMIT);
                    $userIdList = array();
                    $list = array();
                    
                    foreach ( $_result as $val )
                    {
                        $userIdList[] = $val['id'];
                        $list[$val['id']] = $val;
                    }

                    $result = array( 
                        'type' => 'user', 
                        'list' => $list,
                        'avatarData' => BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, TRUE, FALSE, FALSE, FALSE)
                    );
                    break;
            }
        }
        else
        {
            if ( mb_strlen($searchVal) < $this->indexDao->getMinWordLen() )
            {
                $list = $this->getSearchResultListByDescription($searchVal, self::SEARCH_LIMIT);
            }
            else
            {
                $list = array();
                $arr = array();
                
                foreach ( explode(' ', $searchVal) as $val )
                {
                    $arr[] = '+' . trim($val) . '*';
                }
                
                $data = $this->indexDao->findIndexedData(implode(' ', $arr), array(self::ENTITY_TYPE_PHOTO));
                $arrHash = array();
        
                foreach ( $data as $index )
                {
                    $label = $index->content;

                    if ( mb_strlen($label) > 50 )
                    {
                        $i = mb_stripos($label, $searchVal);
                        $label = '...' . mb_substr($label, ($i - 10) < 0 ? 0 : $i - 10, 50) . '...';
                    }

                    $hash = md5($label);

                    if ( array_key_exists($hash, $arrHash) === FALSE )
                    {
                        $list[$index->entityId] = array('id' => $index->entityId, 'label' => $label, 'count' => 1, 'ids' => $index->entityId);
                        $arrHash[$hash] = $index->entityId;
                    }
                    else
                    {
                        $list[$arrHash[$hash]]['count']++;
                        $list[$arrHash[$hash]]['ids'] .= ',' . $index->entityId;
                    }
                }
            }
            
            $result = array('type' => 'desc', 'list' => $list);
        }

        try
        {
            $cache = new PHOTO_BOL_PhotoCache();
            $cache->key = $this->photoCacheDao->getKey($searchVal);
            $cache->data = json_encode($result);
            $cache->createTimestamp = time();
            $this->photoCacheDao->save($cache);
        }
        catch ( Exception $e ){}
        
        return $result;
    }
    
    public function getSearchAllResult( $searchVal )
    {
        if ( empty($searchVal) )
        {
            return array('result' => TRUE, 'list' => array());
        }
        
        if ( ($cache = $this->photoCacheDao->findCacheByKey($this->photoCacheDao->getKeyAll($searchVal))) !== NULL )
        {
            $data = json_decode($cache->data);
            $ids = $data->ids;
            $type = $data->type;
        }
        else
        {
            $ids = array();

            if ( preg_match('/^(?:#|@)\S+/', $searchVal) === 1 )
            {
                switch ( $searchVal[0] )
                {
                    case '#':
                        $tagIdList = $this->photoDao->getSearchResultAllListByTag($searchVal);
                        $ids = $this->photoDao->getPhotoIdListByTagIdList($tagIdList);
                        $type = 'hash';
                        break;
                    case '@':
                        $userIdList = $this->getSearchResultAllListByUsername(trim($searchVal, '@'));
                        $ids = $this->photoDao->findPhotoIdListByUserIdList($userIdList);
                        $type = 'user';
                        break;
                }
            }
            else
            {
                if ( mb_strlen($searchVal) < PHOTO_BOL_SearchIndexDao::getInstance()->getMinWordLen() )
                {
                    $ids = $this->photoDao->getSearchResultAllListByDescription($searchVal);
                }
                else
                {
                    $arr = array();

                    foreach ( explode(' ', $searchVal) as $val )
                    {
                        $arr[] = '+' . trim($val) . '*';
                    }

                    $data = $this->indexDao->findIndexedData(implode(' ', $arr), array(self::ENTITY_TYPE_PHOTO));

                    foreach ( $data as $index )
                    {
                        $ids[] = $index->entityId;
                    }
                }
                
                $type = 'desc';
            }
            
            try
            {
                $cache = new PHOTO_BOL_PhotoCache();
                $cache->key = $this->photoCacheDao->getKeyAll($searchVal);
                $cache->data = json_encode(array('type' => $type, 'ids' => $ids));
                $cache->createTimestamp = time();
                $this->photoCacheDao->save($cache);
            }
            catch ( Exception $e ){}
        }
        
        return array('type' => $type, 'ids' => $ids);
    }
    
    public function getResultIdList( $searchVal, $id, $listType )
    {
        static $listArr = array();
        
        if ( array_key_exists(($crc = crc32($searchVal)), $listArr) )
        {
            return $listArr[$crc];
        }
        
        if ( $listType == 'all' )
        {
            if ( ($cache = $this->photoCacheDao->findCacheByKey($this->photoCacheDao->getKeyAll($searchVal))) !== NULL )
            {
                $cache->createTimestamp = time();
                $this->photoCacheDao->save($cache);

                $listCache = json_decode($cache->data, TRUE);
                $list = $listCache['ids'];
            }
            else
            {
                $result = $this->getSearchAllResult($searchVal);
                $list = $result['ids'];
            }
        }
        else
        {
            if ( ($cache = $this->photoCacheDao->findCacheByKey($this->photoCacheDao->getKey($searchVal))) !== NULL )
            {
                $cache->createTimestamp = time();
                $this->photoCacheDao->save($cache);

                $listCache =  json_decode($cache->data, TRUE);
                $list = explode(',', $listCache['list'][$id]['ids']);
            }
            else
            {
                $result = $this->getSearchResult($searchVal);
                $list = explode(',', $result['list'][$id]['ids']);
            }
        }
        
        $listArr[$crc] = $list;
        
        return $list;
    }

    public function getPrevPhotoIdList( $listType, $photoId, $data )
    {
        return $this->getResultIdList($data['searchVal'], $data['id'], $listType);
    }

    public function getFirstPhotoIdList( $listType, $photoId, $data )
    {
        return $this->getResultIdList($data['searchVal'], $data['id'], $listType);
    }
    
    public function getNextPhotoIdList( $listType, $photoId, $data )
    {
        return $this->getResultIdList($data['searchVal'], $data['id'], $listType);
    }
    
    public function getLastPhotoIdList( $listType, $photoId, $data )
    {
        return $this->getResultIdList($data['searchVal'], $data['id'], $listType);
    }
}
