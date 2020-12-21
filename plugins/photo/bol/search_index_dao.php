<?php

class PHOTO_BOL_SearchIndexDao extends PEEP_BaseDao
{
    CONST ENTITY_TYPE_ID = 'entityTypeId';
    CONST ENTITY_ID = 'entityId';
    CONST CONTENT = 'content';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'photo_search_index';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchIndex';
    }
    
    public function getMinWordLen()
    {
        static $ftMinWordLen = NULL;
        
        if ( $ftMinWordLen === NULL )
        {
            $len = $this->dbo->queryForRow('SHOW VARIABLES LIKE "ft_min_word_len"');
            $ftMinWordLen = (int)$len['Value'];
        }
        
        return $ftMinWordLen;
    }
    
    public function findIndexedData( $searchVal, array $entityTypes = array(), $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));

        $sql = 'SELECT `index`.*
            FROM `' . $this->getTableName() . '` AS `index`
                INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`index`.`entityId` = `p`.`id`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE MATCH(`index`.`' . self::CONTENT . '`) AGAINST(:val IN BOOLEAN MODE) AND `p`.`privacy` = :everybody AND `p`.`status` = :status AND ' . $condition['where'];
        
        if ( count($entityTypes) !== 0 )
        {
            $sql .= ' AND `index`.`' . self::ENTITY_TYPE_ID . '` IN (SELECT `entity`.`id`
                FROM `' . PHOTO_BOL_SearchEntityTypeDao::getInstance()->getTableName() . '` AS `entity`
                WHERE `entity`.`' . PHOTO_BOL_SearchEntityTypeDao::ENTITY_TYPE . '` IN( ' . $this->dbo->mergeInClause($entityTypes) . '))';
        }
        
        $sql .= ' LIMIT :limit';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge($condition['params'], array('val' => $searchVal, 'limit' => (int)$limit, 'everybody' => PHOTO_BOL_PhotoDao::PRIVACY_EVERYBODY, 'status' => 'approved')));
    }
    
    public function deleteIndexItem( $entityTypeId, $entityId )
    {
        if ( empty($entityTypeId) || empty($entityId) )
        {
            return FALSE;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_TYPE_ID, $entityTypeId);
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        
        return $this->deleteByExample($example);
    }
}
