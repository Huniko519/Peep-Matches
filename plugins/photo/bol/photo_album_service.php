<?php

final class PHOTO_BOL_PhotoAlbumService
{
    /**
     * @var PHOTO_BOL_PhotoAlbumDao
     */
    private $photoAlbumDao;
    /**
     * @var PHOTO_BOL_PhotoDao
     */
    private $photoDao;
    /**
     * Class instance
     *
     * @var PHOTO_BOL_PhotoAlbumService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $this->photoDao = PHOTO_BOL_PhotoDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PHOTO_BOL_PhotoAlbumService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Finds album by id
     *
     * @param int $id
     * @return PHOTO_BOL_PhotoAlbum
     */
    public function findAlbumById( $id )
    {
        return $this->photoAlbumDao->findById($id);
    }
    
    public function countAlbums()
    {
        return $this->photoAlbumDao->countAll();
    }

    /**
     * Finds album by name
     *
     * @param string $name
     * @param int $userId
     * @return PHOTO_BOL_PhotoAlbum
     */
    public function findAlbumByName( $name, $userId )
    {
        return $this->photoAlbumDao->findAlbumByName($name, $userId);
    }

    /**
     * Finds entity album by name
     *
     * @param string $name
     * @param $entityId
     * @param string $entityType
     * @return PHOTO_BOL_PhotoAlbum
     */
    public function findEntityAlbumByName( $name, $entityId, $entityType = 'user' )
    {
        return $this->photoAlbumDao->findEntityAlbumByName($name, $entityId, $entityType);
    }

    /**
     * Counts entity albums
     *
     * @param $entityId
     * @param string $entityType
     * @return int
     */
    public function countEntityAlbums( $entityId, $entityType = 'user' )
    {
        return $this->photoAlbumDao->countEntityAlbums($entityId, $entityType);
    }

    /**
     * Counts user albums
     *
     * @param $userId
     * @param null $exclude
     * @param bool $excludeEmpty
     * @internal param string $type
     * @return int
     */
    public function countUserAlbums( $userId, $exclude = null, $excludeEmpty = false )
    {
        return $this->photoAlbumDao->countAlbums($userId, $exclude, $excludeEmpty);
    }

    /**
     * Counts photos in the album
     *
     * @param int $albumId
     * @param null $exclude
     * @return int
     */
    public function countAlbumPhotos( $albumId, $exclude = null )
    {
        return $this->photoDao->countAlbumPhotos($albumId, $exclude);
    }

    /**
     * Returns user's photo albums list
     *
     * @param $entityId
     * @param $entityType
     * @param int $page
     * @param int $limit
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function findEntityAlbumList( $entityId, $entityType, $page, $limit )
    {
        $albums = $this->photoAlbumDao->getEntityAlbumList($entityId, $entityType, $page, $limit);

        $list = array();

        if ( $albums )
        {
            $albumIdList = $albumList = array();
            foreach ( $albums as $key => $album )
            {
                array_push($albumIdList, $album->id);
                $list[$key]['dto'] = $album;
                $albumList[] = get_object_vars($album);
            }
            
            $covers = $this->getAlbumCoverForList($albumList);
            $counters = $this->countAlbumPhotosForList($albumIdList);
            foreach ( $albums as $key => $album )
            {
                $list[$key]['cover'] = $covers[$album->id];
                $list[$key]['photo_count'] = $counters[$album->id];
            }
        }

        return $list;
    }
    
    /**
     * Returns user's photo albums list
     *
     * @param int $userId
     * @param int $page
     * @param int $limit
     * @param null $exclude
     * @return array of PHOTO_BOL_PhotoAlbum
     */
    public function findUserAlbumList( $userId, $page, $limit, $exclude = null, $includeCount = false )
    {
        $albums = $this->photoAlbumDao->getUserAlbumList($userId, $page, $limit, $exclude);

        $list = array();

        if ( $albums )
        {
            $albumIdList = array();
            $albumList = array();
            
            foreach ( $albums as $key => $album )
            {
                $albumIdList[] = $album->id;
                $list[$key]['dto'] = $album;
                $albumList[] = get_object_vars($album);
            }
         
            $covers = $this->getAlbumCoverForList($albumList);

            if ( $includeCount )
            {
                $counters = $this->countAlbumPhotosForList($albumIdList);
            }
            else
            {
                $counters = array_fill_keys(array_keys($covers), 0);
            }
            
            foreach ( $albums as $key => $album )
            {
                $list[$key]['cover'] = $covers[$album->id];
                $list[$key]['photo_count'] = $counters[$album->id];
            }
        }

        return $list;
    }
    
    public function getUserAlbumList( $userId, $page, $limit, array $exclude = array() )
    {
        $first = ($page - 1) * $limit;
        $albums = $this->photoAlbumDao->findUserAlbumList($userId, $first, $limit, $exclude);

        $list = array();

        if ( $albums )
        {            
            $covers = $this->getAlbumCoverForList($albums);

            foreach ( $albums as $key => $album )
            {
                $list[$key] = $album;
                $list[$key]['url'] = $covers[$album['id']];
            }
        }

        return $list;
    }
    
    public function findUserAlbums( $userId, $offset, $limit )
    {
        return $this->photoAlbumDao->getUserAlbums($userId, $offset, $limit);
    }

    public function findEntityAlbums( $entityId, $entityType, $offset, $limit )
    {
        return $this->photoAlbumDao->getEntityAlbums($entityId, $entityType, $offset, $limit);
    }
    
    public function countAlbumPhotosForList( array $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }
        
        $counters = $this->photoDao->countAlbumPhotosForList($albumIdList);
        
        $counterList = array();
        if ( $counters )
        {
            foreach ( $counters as $count )
            {
                $counterList[$count['albumId']] = $count['photoCount'];
            }
        }
        
        $result = array();
        foreach ( $albumIdList as $albumId )
        {
            $result[$albumId] = !empty($counterList[$albumId]) ? $counterList[$albumId] : null;
        }
        
        return $result;
    }
    
    /**
     * Get album cover - album first image URL
     *
     * @param int $albumId
     * @return string
     */
    public function getAlbumCover( $albumId, $orig = FALSE )
    {
        return PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($albumId, $orig);
    }
    
    public function getAlbumCoverForList( array $albumList )
    {
        if ( !$albumList )
        {
            return array();
        }
        
        $albumIdList = array();
        
        foreach ( $albumList as $album )
        {
            $albumIdList[] = $album['id'];
        }
        
        $covers = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlListForAlbumIdList($albumIdList);
        
        foreach ( $this->photoDao->getLastPhotoForList(array_diff($albumIdList, array_keys($covers))) as $photo )
        {
            $covers[$photo->albumId] = $this->photoDao->getPhotoUrl($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_PREVIEW, !empty($photo->dimension) ? $photo->dimension : FALSE);
        }
        
        foreach ( array_diff($albumIdList, array_keys($covers)) as $id )
        {
            $covers[$id] = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverDefaultUrl();
        }
        
        return $covers;
    }

    /**
     * Deletes user albums
     * 
     * 
     * @param int $userId
     * @return boolean
     */
    public function deleteUserAlbums( $userId )
    {
        return $this->deleteEntityAlbums($userId, 'user');
    }
    
    public function deleteEntityAlbums( $entityId, $entityType = 'user' )
    {
        if ( !$entityId )
        {
            return false;
        }

        $count = $this->countEntityAlbums($entityId, $entityType);

        if ( !$count )
        {
            return true;
        }

        $albums = $this->findEntityAlbumList($entityId, $entityType, 1, $count);

        if ( $albums )
        {
            foreach ( $albums as $album )
            {
                $dto = $album['dto'];
                $this->deleteAlbum($dto->id);
            }
        }

        return true;
    }

    /**
     * Get a list of albums for suggest
     *
     * @param int $userId
     * @param string $query
     * @return array of PHOTO_Bol_PhotoAlbum
     */
    public function suggestUserAlbums( $userId, $query = '' )
    {
        return $this->photoAlbumDao->suggestUserAlbums($userId, $query);
    }
    
    /**
     * Get a list of albums for suggest
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $query
     * @return array of PHOTO_Bol_PhotoAlbum
     */
    public function suggestEntityAlbums( $entityType, $entityId, $query = '' )
    {
        return $this->photoAlbumDao->suggestEntityAlbums($entityType, $entityId, $query);
    }

    /**
     * Get album update time - time when last photo was added
     *
     * @param int $albumId
     * @return int
     */
    public function getAlbumUpdateTime( $albumId )
    {
        $lastPhoto = $this->photoDao->getLastPhoto($albumId);

        return $lastPhoto ? $lastPhoto->addDatetime : null;
    }

    /**
     * Adds photo album
     *
     * @param PHOTO_BOL_PhotoAlbum $album
     * @return int
     */
    public function addAlbum( PHOTO_BOL_PhotoAlbum $album )
    {
        if ( $album->entityId == null )
        {
            $album->entityId = $album->userId;
        }

        $this->photoAlbumDao->save($album);

        $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_ALBUM_ADD, array('id' => $album->id));
        PEEP::getEventManager()->trigger($event);

        return $album->id;
    }

    /**
     * Updates photo album
     *
     * @param PHOTO_BOL_PhotoAlbum $album
     * @return int
     */
    public function updateAlbum( PHOTO_BOL_PhotoAlbum $album )
    {
        $this->photoAlbumDao->save($album);

        $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_ALBUM_EDIT, array('id' => $album->id));
        PEEP::getEventManager()->trigger($event);

        return $album->id;
    }

    /**
     * Deletes photo album
     * 
     * @param int $albumId
     * @return boolean
     */
    public function deleteAlbum( $albumId )
    {
        if ( !$albumId )
        {
            return false;
        }

        $album = $this->findAlbumById($albumId);

        if ( $album )
        {
            $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_BEFORE_ALBUM_DELETE, array('id' => $albumId));
            PEEP::getEventManager()->trigger($event);

            $photos = $this->photoDao->getAlbumAllPhotos($albumId);

            $photoService = PHOTO_BOL_PhotoService::getInstance();

            foreach ( $photos as $photo )
            {
                $photoService->deletePhoto($photo->id, TRUE);
            }

            $deleted = $this->photoAlbumDao->deleteById($albumId);
            
            return $deleted;
        }

        return true;
    }
    
    public function deleteAlbums( $limit )
    {
        $albums = $this->photoAlbumDao->getAlbumsForDelete($limit);
        
        if ( $albums )
        {
            foreach ( $albums as $albumId )
            {
                $this->deleteAlbum($albumId);
            }
        }
    }
    
    public function updatePhotosPrivacy( $userId, $privacy )
    {
        $albumIdList = $this->photoAlbumDao->getUserAlbumIdList($userId);

        if ( !$albumIdList )
        {
            return;
        }
        
        $this->photoDao->updatePrivacyByAlbumIdList($albumIdList, $privacy);

        PHOTO_BOL_PhotoService::getInstance()->cleanListCache();
        
        foreach ( $albumIdList as $albumId ) 
        {
            $photos = $this->photoDao->getAlbumAllPhotos($albumId);
            
            if ( empty($photos) )
            {
                continue;
            }
            
            $idList = array();
            foreach ( $photos as $photo )
            {
                array_push($idList, $photo->id);
            }
            
            $status = $privacy == 'everybody';
            $event = new PEEP_Event(
                'base.update_entity_items_status', 
                array('entityType' => 'photo_rates', 'entityIds' => $idList, 'status' => $status)
            );

            PEEP::getEventManager()->trigger($event);
        }
    }
    
    public function findAlbumNameListByIdList( array $idList )
    {
        return $this->photoAlbumDao->findAlbumNameListByIdList($idList);
    }
    
    public function isAlbumOwner( $albumId, $userId )
    {
        return $this->photoAlbumDao->isAlbumOwner($albumId, $userId);
    }
    
    public function getLastPhotoByAlbumId( $albumId )
    {
        return $this->photoDao->getLastPhoto($albumId);
    }
    
    public function cropAlbumCover( PHOTO_BOL_PhotoAlbum $album, $coords, $viewSize, $photoId = 0 )
    {
        if ( !empty($photoId) && ($photo = $this->photoDao->findById($photoId)) !== NULL )
        {
            $path = $this->photoDao->getPhotoPath($photo->id, $photo->hash, 'main');
        }
        else
        {
            $path = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverPathByAlbumId($album->id);
        }
        
        $storage = PEEP::getStorage();
        $tmpPath = PEEP::getPluginManager()->getPlugin('photo')->getPluginFilesDir() . uniqid(time(), TRUE) . '.jpg';
        $storage->copyFileToLocalFS($path, $tmpPath);
        
        if ( ($coverDto = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->findByAlbumId($album->id)) === NULL )
        {
            $coverDto = new PHOTO_BOL_PhotoAlbumCover();
            $coverDto->albumId = $album->id;
            PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->save($coverDto);
        }
        
        $oldCover = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverPathForCoverEntity($coverDto);
        $oldCoverOrig = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverOrigPathForCoverEntity($coverDto);

        $coverDto->hash = uniqid();
        $coverDto->auto = 0;

        try
        {
            $image = new UTIL_Image($tmpPath);

            if ( $image->getWidth() >= $coords['w'] && $coords['w'] > 0 && $image->getHeight() >= $coords['h'] && $coords['h'] > 0 )
            {
                $width = $image->getWidth();
                $k = $width / $viewSize;

                $image->cropImage($coords['x'] * $k, $coords['y'] * $k, $coords['w'] * $k, $coords['h'] * $k);
            }
            
            $saveImage = PEEP::getPluginManager()->getPlugin('photo')->getPluginFilesDir() . uniqid(time(), TRUE) . '.jpg';
            $image->saveImage($saveImage);
            $image->destroy();

            $storage->copyFile($saveImage, PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverPathForCoverEntity($coverDto));
            $storage->copyFile($tmpPath, PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverOrigPathForCoverEntity($coverDto));

            $storage->removeFile($oldCover);
            $storage->removeFile($oldCoverOrig);

            @unlink($saveImage);
            @unlink($tmpPath);
            
            PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->save($coverDto);
        }
        catch ( Exception $e )
        {
            return FALSE;
        }

        return array(
            'cover' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlForCoverEntity($coverDto),
            'coverOrig' => PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverOrigUrlForCoverEntity($coverDto)
        );
    }
    
    public function findAlbumNameListByUserId( $userId, array $excludeIdList = array() )
    {
        return $this->photoAlbumDao->findAlbumNameListByUserId( $userId, $excludeIdList );
    }
    
    public function getCnewsAlbum( $userId )
    {
        if ( empty($userId) )
        {
            if ( !PEEP::getUser()->isAuthenticated() )
            {
                return NULL;
            }
            else
            {
                $userId = PEEP::getUser()->getId();
            }
        }
        
        return $this->findAlbumByName(trim(PEEP::getLanguage()->text('photo', 'cnews_album')), $userId);
    }
    
    public function isCnewsAlbum( $mixed, $userId = NULL )
    {
        if ( empty($mixed) )
        {
            return FALSE;
        }
        
        if ( is_int($mixed) )
        {
            $album = $this->findAlbumById($mixed);
        }
        elseif ( is_string($mixed) && !empty($userId) )
        {
            $album = $this->findAlbumByName($mixed, $userId);
        }
        elseif ( $mixed instanceof PHOTO_BOL_PhotoAlbum )
        {
            $album = $mixed;
        }
        
        return !empty($album) && strcasecmp(trim(PEEP::getLanguage()->text('photo', 'cnews_album')), trim($album->name)) === 0;
    }
}
