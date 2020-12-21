<?php

class PHOTO_BOL_PhotoAlbumCoverDao extends PEEP_BaseDao
{
    CONST ALBUM_ID = 'albumId';
    CONST AUTO = 'auto';
    
    CONST PREFIX_ALBUM_COVER = 'cover_';
    CONST PREFIX_ALBUM_COVER_ORIG = 'cover_orig_';
    CONST PREFIX_ALBUM_COVER_DEFAULT = 'album_no_cover.png';
    
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
        return PEEP_DB_PREFIX . 'photo_album_cover';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_PhotoAlbumCover';
    }

    public function isAlbumCoverExist( $albumId )
    {
        if ( empty($albumId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::ALBUM_ID . '` = :albumId';
        
        return (int)$this->dbo->queryForColumn($sql, array('albumId' => $albumId)) > 0;
    }

    public function findByAlbumId( $albumId )
    {
        if ( empty($albumId) )
        {
            return NULL;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::ALBUM_ID . '` = :albumId';
        
        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('albumId' => $albumId));
    }

    public function getAlbumCoverUrlByAlbumId( $albumId, $orig = FALSE )
    {
        if ( empty($albumId) )
        {
            return NULL;
        }
        
        if ( ($cover = $this->findByAlbumId($albumId)) === NULL )
        {
            if ( ($photo = PHOTO_BOL_PhotoAlbumService::getInstance()->getLastPhotoByAlbumId($albumId)) !== NULL )
            {
                return PHOTO_BOL_PhotoDao::getInstance()->getPhotoUrl($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_PREVIEW, !empty($photo->dimension) ? $photo->dimension : FALSE);
            }
            else
            {
                return $this->getAlbumCoverDefaultUrl();
            }
        }
        else
        {
            if ( $orig )
            {
                return PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER_ORIG . $cover->id . '_' . $cover->hash . '.jpg');
            }

            return PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER . $cover->id . '_' . $cover->hash . '.jpg');
        }
    }
    
    public function getAlbumCoverPathByAlbumId( $albumId )
    {
        if ( empty($albumId) || ($cover = $this->findByAlbumId($albumId)) === NULL )
        {
            $lastPhoto = PHOTO_BOL_PhotoAlbumService::getInstance()->getLastPhotoByAlbumId($albumId);
            
            return PHOTO_BOL_PhotoDao::getInstance()->getPhotoPath($lastPhoto->id, $lastPhoto->hash, 'main');
        }
        
        return PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER_ORIG . $cover->id . '_' . $cover->hash . '.jpg';
    }

    public function getAlbumCoverUrlForCoverEntity( PHOTO_BOL_PhotoAlbumCover $cover )
    {
        return PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER . $cover->id . '_' . $cover->hash . '.jpg');
    }
    
    public function getAlbumCoverOrigUrlForCoverEntity( PHOTO_BOL_PhotoAlbumCover $cover )
    {
        return PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER_ORIG . $cover->id . '_' . $cover->hash . '.jpg');
    }

    public function getAlbumCoverDefaultUrl()
    {
        static $url = NULL;
        
        if ( $url === NULL )
        {
            $url = PEEP_URL_STATIC_THEMES . PEEP::getConfig()->getValue('base', 'selectedTheme') . '/images/' . self::PREFIX_ALBUM_COVER_DEFAULT;
        }
        
        return $url;
    }
    
    public function getAlbumCoverPathForCoverEntity( PHOTO_BOL_PhotoAlbumCover $cover )
    {
        return PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER . $cover->id . '_' . $cover->hash . '.jpg';
    }
    
    public function getAlbumCoverOrigPathForCoverEntity( PHOTO_BOL_PhotoAlbumCover $cover )
    {
        return PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER_ORIG . $cover->id . '_' . $cover->hash . '.jpg';
    }
    
    public function getAlbumCoverUrlListForAlbumIdList( array $albumIdList )
    {
        if ( count($albumIdList) === 0 )
        {
            return array();
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::ALBUM_ID . '` IN (' . implode(',', array_map('intval', $albumIdList)) . ')';
        
        $list = $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
        
        $result = array();
        $storage = PEEP::getStorage();
        $dir = PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir() . self::PREFIX_ALBUM_COVER;

        foreach ( $list as $cover )
        {
            $result[$cover->albumId] = $storage->getFileUrl($dir . $cover->id . '_' . $cover->hash . '.jpg');
        }
        
        return $result;
    }
    
    public function deleteCoverByAlbumId( $albumId )
    {
        if ( empty($albumId) || ($cover = $this->findByAlbumId($albumId)) === NULL )
        {
            return FALSE;
        }
        
        $storate = PEEP::getStorage();
        $storate->removeFile($this->getAlbumCoverPathForCoverEntity($cover));
        $storate->removeFile($this->getAlbumCoverOrigPathForCoverEntity($cover));
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ALBUM_ID, $albumId);
        
        return $this->deleteByExample($example);
    }
}
