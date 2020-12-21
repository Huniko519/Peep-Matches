<?php

class PHOTO_BOL_PhotoDao extends PEEP_BaseDao
{
    CONST PHOTO_PREFIX = 'photo_';
    CONST PHOTO_PREVIEW_PREFIX = 'photo_preview_';
    CONST PHOTO_ORIGINAL_PREFIX = 'photo_original_';
    CONST PHOTO_SMALL_PREFIX = 'photo_small_';
    CONST PHOTO_FULLSCREEN_PREFIX = 'photo_fullscreen_';
    
    const CACHE_TAG_PHOTO_LIST = 'photo.list';
    
    CONST PHOTO_ENTITY_TYPE = 'photo';
    
    CONST PRIVACY = 'privacy';
    CONST PRIVACY_EVERYBODY = 'everybody';
    CONST PRIVACY_FRIENDS_ONLY = 'friends_only';
    CONST PRIVACY_ONLY_FOR_ME = 'only_for_me';

    CONST STATUS_APPROVAL = 'approval';
    CONST STATUS_APPROVED = 'approved';
    CONST STATUS_BLOCKED = 'blocked';

    const ENTITY_TYPE_USER = 'user';
    
    private $typeToPrefix;
    
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class.
     *
     * @return PHOTO_BOL_PhotoDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    protected function __construct()
    {
        parent::__construct();
        
        $this->typeToPrefix = array(
            PHOTO_BOL_PhotoService::TYPE_ORIGINAL => self::PHOTO_ORIGINAL_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_FULLSCREEN => self::PHOTO_FULLSCREEN_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_MAIN => self::PHOTO_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_PREVIEW => self::PHOTO_PREVIEW_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_SMALL => self::PHOTO_SMALL_PREFIX
        );
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_Photo';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'photo';
    }

    /**
     * Get photo/preview URL
     *
     * @param int $id
     * @param string $type
     * @param boolean $preview
     * @return string
     */

    public function getPhotoUrlByType( $id, $type, $hash, $dimension = NULL )
    {
        if ( !isset($this->typeToPrefix[$type]) )
        {
            return NULL;
        }
        
        $storage = PEEP::getStorage();
        $userfilesDir = PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        
        if ( in_array($type, array(PHOTO_BOL_PhotoService::TYPE_FULLSCREEN, PHOTO_BOL_PhotoService::TYPE_PREVIEW, PHOTO_BOL_PhotoService::TYPE_SMALL)) )
        {
            if ( $dimension === NULL )
            {
                $photo = $this->findById($id);
                $dimension = !empty($photo->dimension) ? $photo->dimension : NULL;
            }
            
            if ( empty($dimension) )
            {
                switch ( $type )
                {
                    case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                    case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . '.jpg');
                    case PHOTO_BOL_PhotoService::TYPE_SMALL:
                        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug . '.jpg');
                }
            }
        }
        
        return $storage->getFileUrl($userfilesDir . $this->typeToPrefix[$type] . $id . $hashSlug . '.jpg');
    }

    /**
     * Get photo/preview URL
     *
     * @param int $id
     * @param $hash
     * @param boolean $preview
     * @return string
     */
    public function getPhotoUrl( $id, $hash, $preview = false, $dimension = NULL )
    {
        $storage = PEEP::getStorage();
        $userfilesDir = PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        
        if ( $preview )
        {
            if ( $dimension === NULL )
            {
                $photo = $this->findById($id);
                $dimension = !empty($photo->dimension) ? $photo->dimension : NULL;
            }
            
            if ( empty($dimension) )
            {
                return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . '.jpg');
            }
            else
            {
                return $storage->getFileUrl($userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug . '.jpg');
            }
        }
        
        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . '.jpg');
    }

    public function getPhotoFullsizeUrl( $id, $hash )
    {
        $userfilesDir = PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $storage = PEEP::getStorage();
        $hashSlug = !empty($hash) ? '_' . $hash : '';

        return $storage->getFileUrl($userfilesDir . self::PHOTO_ORIGINAL_PREFIX . $id . $hashSlug . '.jpg');
    }

    /**
     * Get directory where 'photo' plugin images are uploaded
     *
     * @return string
     */
    public function getPhotoUploadDir()
    {
        return PEEP::getPluginManager()->getPlugin('photo')->getUserFilesDir();
    }

    /**
     * Get path to photo in file system
     *
     * @param int $photoId
     * @param $hash
     * @param string $type
     * @return string
     */
    public function getPhotoPath( $photoId, $hash, $type = '' )
    {
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        $ext = '.jpg';
        
        switch ( $type )
        {
            case PHOTO_BOL_PhotoService::TYPE_MAIN:
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                return $this->getPhotoUploadDir() . self::PHOTO_PREVIEW_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_ORIGINAL:
                return $this->getPhotoUploadDir() . self::PHOTO_ORIGINAL_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_SMALL:
                return $this->getPhotoUploadDir() . self::PHOTO_SMALL_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                return $this->getPhotoUploadDir() . self::PHOTO_FULLSCREEN_PREFIX . $photoId . $hashSlug . $ext;
            default:
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . $ext;
        }
    }

    public function getPhotoPluginFilesPath( $photoId, $type = '' )
    {
        $dir = $this->getPhotoPluginFilesDir();

        switch ( $type )
        {
            case PHOTO_BOL_PhotoService::TYPE_MAIN:
                return $dir . self::PHOTO_PREFIX . $photoId . '.jpg';
            case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                return $dir . self::PHOTO_PREVIEW_PREFIX . $photoId . '.jpg';
            case PHOTO_BOL_PhotoService::TYPE_ORIGINAL:
                return $dir . self::PHOTO_ORIGINAL_PREFIX . $photoId . '.jpg';
            case PHOTO_BOL_PhotoService::TYPE_SMALL:
                return $dir . self::PHOTO_SMALL_PREFIX . $photoId . '.jpg';
            case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                return $dir . self::PHOTO_FULLSCREEN_PREFIX . $photoId . '.jpg';
            default:
                return $dir . self::PHOTO_PREFIX . $photoId . '.jpg';
        }
    }

    public function getPhotoPluginFilesDir()
    {
        return PEEP::getPluginManager()->getPlugin('photo')->getPluginFilesDir();
    }

    /**
     * Find photo owner
     *
     * @param int $id
     * @return int
     */
    public function findOwner( $id )
    {
        if ( !$id )
            return null;

        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `a`.`userId`       
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            WHERE `p`.`id` = :id
            LIMIT 1
        ";

        $qParams = array('id' => $id);

        $owner = $this->dbo->queryForColumn($query, $qParams);

        return $owner;
    }

    /**
     * Get photo list (featured|latest|toprated)
     *
     * @param string $listType
     * @param int $page
     * @param int $limit
     * @param bool $checkPrivacy
     * @param null $exclude
     * @return array
     */
    public function getPhotoList( $listType, $first, $limit, $exclude = NULL, $checkPrivacy = NULL )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType,
            array(
                'photo' => 'p',
                'featured' => 'f',
                'album' => 'a'
            ),
            array(
                'listType' => $listType,
                'first' => $first,
                'limit' => $limit,
                'exclude' => $exclude,
                'checkPrivacy' => $checkPrivacy
            ));

        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        switch ( $listType )
        {
            case 'featured':
                $photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = 'SELECT `p`.*, `a`.`userId`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                        INNER JOIN `' . $photoFeaturedDao->getTableName() . '` AS `f` ON (`f`.`photoId`=`p`.`id`)
                        ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $excludeCond . ' AND `f`.`id` IS NOT NULL
                        AND `a`.`entityType` = :user ' .
                        ($checkPrivacy !== NULL ? 
                            $checkPrivacy ? ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)' : 
                                            ' AND `p`.`' . self::PRIVACY . '` = :everybody' : '') . ' AND
                        ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
            case 'latest':
            default:
                $query = 'SELECT `p`.*, `a`.`userId`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
                        ' . $condition['join'] . '
                    WHERE `a`.`entityType` = :user AND `p`.`status` = :status'  . $excludeCond . 
                        ($checkPrivacy !== NULL ? 
                            $checkPrivacy ? ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)' : 
                                            ' AND `p`.`' . self::PRIVACY . '` = :everybody' : '') . ' AND
                        ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
        }
        
        $params = array('user' => 'user', 'first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved');
        
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                case FALSE:
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
            }
        }
        
        return $this->dbo->queryForList($query, array_merge($params, $condition['params']));
    }

    public function findAlbumPhotoList( $albumId, $listType, $offset, $limit, $privacy = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition(sprintf('findAlbumPhotoList.%s', $listType),
            array(
                'photo' => 'p',
                'featured' => 'f',
                'album' => 'a'
            ),
            array(
                'albumId' => $albumId,
                'listType' => $listType,
                'offset' => $offset,
                'limit' => $limit,
                'privacy' => $privacy
            ));

        $privacySql = $privacy === null ? "1": "`p`.`privacy`='{$privacy}'";
        
        switch ( $listType )
        {
            case 'featured':
                $query = 'SELECT `p`.*
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f` ON (`f`.`photoId`=`p`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . '
                    AND `f`.`id` IS NOT NULL AND ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
            case 'toprated':
                $query = 'SELECT `p`.*, `r`.`' . BOL_RateDao::ENTITY_ID . '`, COUNT(`r`.id) as `ratesCount`, AVG(`r`.`score`) as `avgScore`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                        INNER JOIN ' . BOL_RateDao::getInstance()->getTableName() . ' AS `r` ON (`r`.`entityId`=`p`.`id`
                            AND `r`.`' . BOL_RateDao::ENTITY_TYPE . '` = "photo_rates" AND `r`.`' . BOL_RateDao::ACTIVE . '` = 1)
                        ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . ' AND ' . $condition['where'] . '
                    GROUP BY `p`.`id`
                    ORDER BY `avgScore` DESC, `ratesCount` DESC
                    LIMIT :first, :limit';
                break;
            case 'latest':
            default:
                $query = 'SELECT `p`.*
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . ' AND ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
        }

        return $this->dbo->queryForList($query, array_merge(
            array(
                'albumId' => $albumId,
                'first' => $offset,
                'limit' => $limit,
                'status' => self::STATUS_APPROVED
            ),
            $condition['params']
        ));
    }
    
    public function getAlbumPhotoList( $albumId, $offset, $limit, $checkPrivacy = NULL, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
            WHERE `p`.`albumId` = :albumId AND `p`.`status` = :status' . 
                ($checkPrivacy !== NULL ? 
                    $checkPrivacy ? ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)' : 
                                    ' AND `p`.`' . self::PRIVACY . '` = :everybody' : '') . 
                (count($exclude) !== 0 ? ' AND `p`.`id` NOT IN (' . implode(',', array_map('intval', array_unique($exclude))) . ')' : '') . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array('albumId' => $albumId, 'first' => (int)$offset, 'limit' => (int)$limit, 'status' => 'approved');
        
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                case FALSE:
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
            }
        }
        
        return $this->dbo->queryForList($sql, $params);
    }

    public function findPhotoInfoListByIdList( $idList, $listType = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType,
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'idList' => $idList,
                'listType' => $listType
            )
        );

        $query = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`id` IN (' . $this->dbo->mergeInClause($idList) . ') AND `p`.`status` = :status AND ' . $condition['where'] . '
            ORDER BY `id` DESC';

        return $this->dbo->queryForList($query, array_merge(
            array('status' => self::STATUS_APPROVED),
            $condition['params']
        ));
    }

    /**
     * Count photos
     *
     * @param string $listType
     * @param boolean $checkPrivacy
     * @param null $exclude
     * @return int
     */
    public function countPhotos( $listType, $checkPrivacy = true, $exclude = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countPhotos',
            array(
                'photo' => 'p',
                'album' => 'a',
                'featured' => 'f'
            ),
            array(
                'listType' => $listType,
                'checkPrivacy' => $checkPrivacy,
                'exclude' => $exclude
            )
        );

        $privacyCond = $checkPrivacy ? " AND `p`.`privacy` = 'everybody' " : "";
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        switch ( $listType )
        {
            case 'featured':
                $featuredDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = 'SELECT COUNT(`p`.`id`)
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . $albumDao->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    INNER JOIN `' . $featuredDao->getTableName() . '` AS `f` ON ( `p`.`id` = `f`.`photoId` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $privacyCond . $excludeCond . ' AND `f`.`id` IS NOT NULL
                    AND `a`.`entityType` = :entityType AND ' . $condition['where'];
                break;

            case 'latest':
            default:
                $query = 'SELECT COUNT(`p`.`id`)
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . $albumDao->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $privacyCond . $excludeCond . '
                    AND `a`.`entityType` = :entityType AND ' . $condition['where'];
                break;
        }

        return $this->dbo->queryForColumn($query, array_merge(
            array(
                'status' => self::STATUS_APPROVED,
                'entityType' => self::ENTITY_TYPE_USER
            ),
            $condition['params']
        ));
    }

    public function countFullsizePhotos()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->countByExample($example);
    }

    public function deleteFullsizePhotos()
    {
        $photos = $this->getFullsizePhotos();

        $storage = PEEP::getStorage();

        foreach ( $photos as $photo )
        {
            $photo->hasFullsize = 0;
            $this->save($photo);

            $path = $this->getPhotoPath($photo->id, $photo->hash, 'original');

            if ( $storage->fileExists($path) )
            {
                $storage->removeFile($path);
            }
        }

        return true;
    }

    public function getFullsizePhotos()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->findListByExample($example);
    }

    /**
     * Counts album photos
     *
     * @param int $albumId
     * @param $exclude
     * @return int
     */
    public function countAlbumPhotos( $albumId, $exclude )
    {
        if ( !$albumId ) return false;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countAlbumPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'exclude' => $exclude
            )
        );

        $sql = 'SELECT COUNT(*)
            FROM `%s` AS `p`
                INNER JOIN `%s` AS `a` ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `p`.`albumId` = :albumId AND `p`.`status` = :status AND
                %s AND
                %s';
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName(),
            $condition['join'],
            $condition['where'],
            !empty($exclude) ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1'
        );

        return (int) $this->dbo->queryForColumn($sql, array_merge(
            array(
                'albumId' => $albumId,
                'status' => self::STATUS_APPROVED
            ),
            $condition['params']
        ));
    }
    
    public function countAlbumPhotosForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }
        
        $sql = "SELECT `albumId`, COUNT(*) AS `photoCount` FROM `".$this->getTableName()."` 
            WHERE `status` = 'approved' 
            AND `albumId` IN (".$this->dbo->mergeInClause($albumIdList).")
            GROUP BY `albumId`";
        
        return $this->dbo->queryForList($sql);
    }

    /**
     * Counts photos uploaded by a user
     *
     * @param int $userId
     * @return int
     */
    public function countUserPhotos( $userId )
    {
        if ( !$userId )
            return false;

        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE `a`.`userId` = :user AND `a`.`entityType` = 'user'
        ";

        return $this->dbo->queryForColumn($query, array('user' => $userId));
    }

    /**
     * Returns photos in the album
     *
     * @param int $albumId
     * @param int $page
     * @param int $limit
     * @param $exclude
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumPhotos( $albumId, $page, $limit, $exclude, $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED )
    {
        if ( !$albumId )
        {
            return false;
        }

        $first = ( $page - 1 ) * $limit;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getAlbumPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'page' => $page,
                'limit' => $limit,
                'exclude' => $exclude,
                'status' => $status
            )
        );

        $sql = 'SELECT `p`.*
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                ' . $condition['join'] . '
            WHERE `p`.`albumId` = :albumId AND
                ' . (!empty($status) ? '`p`.`status` = :status' : '1') . ' AND
                ' . $condition['where'] . ' AND
                ' . (!empty($exclude) ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1') . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array(
            'albumId' => $albumId,
            'first' => $first,
            'limit' => $limit
        );

        if ( !empty($status) )
        {
            $params['status'] = $status;
        }

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            $params,
            $condition['params']
        ));
    }

    /**
     * Returns all photos in the album
     *
     * @param int $albumId
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumAllPhotos( $albumId, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getAlbumAllPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'exclude' => $exclude,
            )
        );

        $sql = 'SELECT `p`.*
            FROM `%s` AS `p`
                INNER JOIN `%s` AS `a` ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `p`.`albumId` = :albumId AND %s AND %s
            ORDER BY `p`.`id` DESC';
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName(),
            $condition['join'],
            count($exclude) !== 0 ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1',
            $condition['where']);

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            array('albumId' => $albumId),
            $condition['params']
        ));
    }
    
    public function getLastPhotoForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }

        $sql = 'SELECT MIN(`b`.`id`)
            FROM `' . $this->getTableName() . '` AS `b`
            WHERE `b`.`status` = :status AND `b`.`privacy` = :privacy
                    AND `b`.`albumId` IN (' . implode(',', array_unique(array_map('intval', $albumIdList))) . ')
            GROUP BY `b`.`albumId` ';

        $photoIdList = $this->dbo->queryForColumnList($sql, array('status' => 'approved', 'privacy' => 'everybody'));

        if ( !$photoIdList )
        {
            return array();
        }

        $sql = 'SELECT `a`.*
            FROM `' . $this->getTableName() . '` AS `a`
            WHERE `a`.`id` IN (' . implode(',', array_unique($photoIdList)) . ')';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
    }

    public function getLastPhoto( $albumId, array $exclude = array() )
    {
        if ( !$albumId )
        {
            return false;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        
        if ( !empty($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }
        
        $example->setOrder('`addDatetime`');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getPreviousPhoto( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new PEEP_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThan('id', $id);
        $example->setOrder('`id` ASC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getNextPhoto( $albumId, $id )
    {
        if ( !$id )
            return false;

        $example = new PEEP_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldLessThan('id', $id);
        $example->andFieldEqual('status', 'approved');
        $example->setOrder('`id` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getPrevPhotoIdList( $listType, $photoId, $checkPrivacy = NULL )
    {
        if ( empty($photoId) )
        {
            return array();
        }
        
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`id` > :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` > :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getNextPhotoIdList( $listType, $photoId, $checkPrivacy = NULL )
    {
        if ( empty($photoId) )
        {
            return array();
        }
        
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`id` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getFirstPhotoIdList( $listType, $checkPrivacy, $photoId )
    {
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getLastPhotoIdList( $listType, $checkPrivacy, $photoId )
    {
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`a`.`id` = `p`.`albumId`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND  `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getPrivacyCondition( $checkPrivacy = NULL )
    {
        $params = array();
        
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $query = ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)';
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
                    break;
                case FALSE:
                    $query = ' AND `p`.`' . self::PRIVACY . '` = :everybody';
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
                    break;
            }
        }
        else
        {
            $query = '';
        }
        
        return array('query' => $query, 'params' => $params);
    }

    /**
     * Returns currently viewed photo index
     *
     * @param int $albumId
     * @param int $id
     * @return int
     */
    public function getPhotoIndex( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new PEEP_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThenOrEqual('id', $id);

        return $this->countByExample($example);
    }

    /**
     * Removes photo file
     *
     * @param int $id
     * @param $hash
     * @param string $type
     */
    public function removePhotoFile( $id, $hash, $type )
    {
        $path = $this->getPhotoPath($id, $hash, $type);

        $storage = PEEP::getStorage();

        if ( $storage->fileExists($path) )
        {
            $storage->removeFile($path);
        }
    }
    
    public function updatePrivacyByAlbumIdList( $albumIdList, $privacy )
    {
        $albums = implode(',', $albumIdList);

        $sql = "UPDATE `".$this->getTableName()."` SET `privacy` = :privacy 
            WHERE `albumId` IN (".$albums.")";
        
        $this->dbo->query($sql, array('privacy' => $privacy));
    }
    
    // Entity photos methods
    
    public function findEntityPhotoList( $entityType, $entityId, $first, $count, $status = "approved", $privacy = null )
    {
        $limit = (int) $count;
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findEntityPhotoList',
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'entityType' => $entityType,
                'entityId' => $entityId,
                'first' => $first,
                'count' => $count,
                'status' => $status,
                'privacy' => $privacy
            )
        );

        $statusSql = $status === null ? "1" : "`p`.`status` = '{$status}'";
        $privacySql = $privacy === null ? "1": "`p`.`privacy`='{$privacy}'";
        
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `p`.*
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            " . $condition['join'] . "
            WHERE $statusSql AND $privacySql
            AND `a`.`entityType` = :entityType
            AND `a`.`entityId` = :entityId
            AND " . $condition['where'] . "
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit";

        $qParams = array(
            'first' => $first,
            'limit' => $limit,
            "entityType" => $entityType,
            "entityId" => $entityId
        );

        $cacheLifeTime = $first == 0 ? 24 * 3600 : null;
        $cacheTags = $first == 0 ? array(self::CACHE_TAG_PHOTO_LIST) : null;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array_merge($qParams, $condition['params']), $cacheLifeTime, $cacheTags);
    }
    
    public function countEntityPhotos( $entityType, $entityId, $status = "approved", $privacy = null )
    {
        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        
        $statusSql = $status === null ? "1" : "`p`.`status` = '{$status}'";
        $privacySql = $privacy === null ? "1": "`p`.`privacy`='{$privacy}'";

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE $statusSql AND $privacySql AND `a`.`entityType` = :entityType AND `a`.`entityId`=:entityId
        ";

        return $this->dbo->queryForColumn($query, array(
            "entityType" => $entityType,
            "entityId" => $entityId
        ));
    }

    public function findPhotoListByUploadKey( $uploadKey, $exclude, $status = null )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uploadKey', $uploadKey);

        if ( $status !== null )
        {
            $example->andFieldEqual('status', $status);
        }

        if ( $exclude && is_array($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        $example->setOrder('`id` DESC');

        return $this->findListByExample($example);
    }
    
    public function findPhotoIdListByUploadKey( $uploadKey, array $exclude = array() )
    {
        if ( empty($uploadKey) )
        {
            return array();
        }
        
        $sql = 'SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `uploadKey` = :key AND `status` = :status';
        
        if ( !empty($exclude) )
        {
            $sql .= ' AND `id` NOT IN(' . implode(',', array_unique(array_map('intval', $exclude))) . ')';
        }
        
        return $this->dbo->queryForColumnList($sql, array('key' => $uploadKey, 'status' => 'approved'));
    }

    public function movePhotosToAlbum( $photoIdList, $albumId, $newAlbum = FALSE )
    {
        if ( empty($photoIdList) || empty($albumId) )
        {
            return FALSE;
        }
        
        $photoIdList = implode(',', array_map('intval', array_unique($photoIdList)));
        $key = PHOTO_BOL_PhotoService::getInstance()->getPhotoUploadKey($albumId);
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `albumId` = :albumId
            WHERE `id` IN (' . $photoIdList . ')';
        
        if ( ($result = $this->dbo->query($sql, array('albumId' => $albumId))) )
        {   
            if ( $newAlbum )
            {
                return $result;
            }
            
            $sql = 'UPDATE `' . $this->getTableName() . '`
                SET `uploadKey` = :key
                WHERE `id` IN(' . $photoIdList . ')';
            $this->dbo->query($sql, array('key' => $key));
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function getSearchResultListByTag( $tag, $limit = PHOTO_BOL_PhotoService::SEARCH_LIMIT )
    {
        if ( empty($tag) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'photo', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));
        
        $sql = 'SELECT `tag`.`label`, COUNT(`entity`.`entityId`) AS `count`, `tag`.`id` AS `id`, GROUP_CONCAT(`entity`.`entityId`) AS `ids`
            FROM `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`tagId` = `tag`.`id`)
                INNER JOIN `' . $this->getTableName() . '` AS `photo` ON(`photo`.`id` = `entity`.`entityId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album`
                    ON(`photo`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `tag`.`label` LIKE :label AND `entity`.`entityType` = :type AND `photo`.`status` = :status AND `photo`.`privacy` = :everybody AND
            ' . $condition['where'] . '
            GROUP BY 1
            ORDER BY `tag`.`label`
            LIMIT :limit';
        
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('label' => '%' . ltrim($tag, '#') . '%', 'type' => self::PHOTO_ENTITY_TYPE, 'limit' => (int)$limit, 'status' => 'approved', 'everybody' => self::PRIVACY_EVERYBODY)));
    }
    
    public function getSearchResultAllListByTag( $tag )
    {
        if ( empty($tag) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'photo', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));
        
        $sql = 'SELECT DISTINCT `tag`.`id`
            FROM `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`tagId` = `tag`.`id`)
                INNER JOIN `' . $this->getTableName() . '` AS `photo` ON(`photo`.`id` = `entity`.`entityId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album` ON(`photo`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `entity`.`entityType` = :entityType AND
            `tag`.`label` LIKE :label AND
            ' . $condition['where'] . '
            ORDER BY `tag`.`label`';
        
        return $this->dbo->queryForColumnList($sql, array_merge($condition['params'], array('entityType' => self::PHOTO_ENTITY_TYPE, 'label' => '%' . ltrim($tag, '#') . '%')));
    }

    public function getPhotoIdListByTagIdList( $tagIdList )
    {
        if ( empty($tagIdList) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));

        $sql = 'SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`entityId` = `p`.`id`)
                INNER JOIN `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag` ON(`tag`.`id` = `entity`.`tagId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album`
                    ON(`p`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `entity`.`entityType` = :entityType AND
            `tag`.`id` IN(' . implode(',', array_map('intval', $tagIdList)) . ') AND
            ' . $condition['where'] . '
            ORDER BY 1 DESC';

        return $this->dbo->queryForColumnList($sql, array_merge($condition['params'], array('entityType' => self::PHOTO_ENTITY_TYPE)));
    }
    
    public function getSearchResultListByUserIdList( $idList, $limit = PHOTO_BOL_PhotoService::SEARCH_LIMIT )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByUser', array('photo' => 'p', 'album' => 'a'));

        $sql = 'SELECT `a`.`userId` AS `id`, COUNT(`p`.`albumId`) AS `count`, GROUP_CONCAT(DISTINCT `p`.`id`) AS `ids`
            FROM `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                INNER JOIN `' . $this->getTableName() . '` AS `p` ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE `a`.`userId` IN (' . implode(',', array_map('intval', $idList)) . ') AND `p`.`status` = :status AND `p`.`privacy` = :everybody AND
            ' . $condition['where'] . '
            GROUP BY 1
            LIMIT :limit';
        
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('limit' => (int)$limit, 'status' => 'approved', 'everybody' => self::PRIVACY_EVERYBODY)));
    }
        
    public function getSearchResultListByDescription( $description, $limit = PHOTO_BOL_PhotoService::SEARCH_LIMIT )
    {
        if ( empty($description) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));

        $sql = 'SELECT `p`.`description` AS `label`, COUNT(`p`.`id`) AS `count`, GROUP_CONCAT(DISTINCT `p`.`id`) AS `ids`, `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                    ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE `p`.`description` LIKE :desc AND `p`.`status` = :status AND `p`.`privacy` = :everybody AND
            ' . $condition['where'] . '
            GROUP BY 1
            LIMIT :limit';
        
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('desc' => '%' . $description . '%', 'limit' => $limit, 'status' => 'approved', 'everybody' => self::PRIVACY_EVERYBODY)));
    }
    
    public function getSearchResultAllListByDescription( $description )
    {
        if ( empty($description) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));

        $sql = 'SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`description` LIKE :desc AND `p`.`privacy` = :everybody AND `p`.`status` = :status AND
            ' . $condition['where'] . '
            ORDER BY `p`.`description`';
        
        return $this->dbo->queryForColumnList($sql, array_merge($condition['params'], array('desc' => '%' . $description . '%', 'everybody' => self::PRIVACY_EVERYBODY, 'status' => 'approved')));
    }
    
    public function findTaggedPhotosByTagId( $tagId, $first, $limit, $checkPrivacy = NULL )
    {
        if ( empty($tagId) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'a'));

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `e` ON(`e`.`entityId` = `p`.`id`)
            ' . $condition['join'] . '
            WHERE `e`.`tagId` = :tagId AND `p`.`status` = :status AND ' . $condition['where'] .
                ($checkPrivacy !== NULL ? 
                    $checkPrivacy ? ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)' : 
                                    ' AND `p`.`' . self::PRIVACY . '` = :everybody' : '') . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
       
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                case FALSE:
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
            }
        }
        
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('tagId' => $tagId, 'first' => (int)$first, 'limit' => (int)$limit, 'everybody' => PHOTO_BOL_PhotoDao::PRIVACY_EVERYBODY, 'status' => 'approved')));
    }
    
    public function findPhotoListByUserId( $userId, $first, $limit, $checkPrivacy = NULL, array $exclude = array(), $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED )
    {
        if ( empty($userId) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findPhotoListByUserId',
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'userId' => $userId,
                'first' => $first,
                'limit' => $limit,
                'checkPrivacy' => $checkPrivacy,
                'exclude' => $exclude,
                'status' => $status
            )
        );

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                ' . $condition['join'] . '
            WHERE `a`.`userId` = :userId AND
                ' . (!empty($status) ? '`p`.`status` = :status' : '1') . ' AND
                ' . ($checkPrivacy !== null ?
                        ($checkPrivacy ?
                                '(`p`.`privacy` = :everybody OR `p`.`privacy` = :friends)' :
                                '`p`.`privacy` = :everybody') :
                        '1') . ' AND
                ' . (!empty($exclude) ? '`p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '1') . ' AND
                ' . $condition['where'] . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array(
            'userId' => $userId,
            'first' => (int) $first,
            'limit' => (int) $limit
        );

        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                case FALSE:
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
            }
        }

        if ( !empty($status) )
        {
            $params['status'] = $status;
        }
        
        return $this->dbo->queryForList($sql, array_merge($params, $condition['params']));
    }
    
    public function findPhotoListByUserIdList( array $userIdList, $first, $limit, $checkPrivacy = NULL )
    {
        if ( count($userIdList) === 0 )
        {
            return array();
        }
        
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
                INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `u` ON (`u`.`id` = `a`.`userId`)
            WHERE `a`.`userId` IN(' . implode(',', array_map('intval', array_unique($userIdList))) . ') AND `p`.`status` = :status' .
                ($checkPrivacy !== NULL ? 
                    $checkPrivacy ? ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)' : 
                                    ' AND `p`.`' . self::PRIVACY . '` = :everybody' : '') . '
            ORDER BY `u`.`username`
            LIMIT :first, :limit';
        
        $params = array('first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved');
        
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                case FALSE:
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
            }
        }
        
        return $this->dbo->queryForList($sql, $params);
    }
    
    public function findPhotoListByDescription( $desc, $id, $first, $limit )
    {
        if ( empty($desc) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));
        
        $sqlIDs = 'SELECT `a1`.`ids`
            FROM (SELECT `p`.`description`, COUNT(*), GROUP_CONCAT(`p`.`id`) AS `ids`, `p`.`id`
                FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                        ON(`a`.`id` = `p`.`albumId`)
                ' . $condition['join'] . '
                WHERE `p`.`description` LIKE :desc AND
                ' . $condition['where'] . '
                GROUP BY 1
                HAVING `p`.`id` = :id) AS `a1`';
        
        $ids = $this->dbo->queryForColumn($sqlIDs, array_merge($condition['params'], array('desc' => '%' . $desc . '%', 'id' => $id)));
        
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
            WHERE `p`.`id` IN (' . $ids . ') AND `p`.`privacy` = :everybody AND `p`.`status` = :status
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
        
        return $this->dbo->queryForList($sql, array('first' => (int)$first, 'limit' => (int)$limit, 'everybody' => self::PRIVACY_EVERYBODY, 'status' => 'approved'));
    }
    
    public function findPhotoListByIdList( array $idList, $first, $limit, $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'a'));
        
        $params = array_merge($condition['params'], array('first' => $first, 'limit' => $limit));
        $statusSql = "1";
        
        if ( !empty($status) )
        {
            $params["status"] = $status;
            $statusSql = "`p`.`status` = :status";
        }
        
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`id` IN (' . implode(',', array_map('intval', array_unique($idList))) . ') AND ' . $statusSql . ' AND ' . $condition['where'] . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
        
        return $this->dbo->queryForList($sql, $params);
    }
    
    public function findPhotoList( $type, $page, $limit, $checkPrivacy = true, $exclude = null)
    {
        if ( $type == 'toprated' )
        {
            $first = ( $page - 1 ) * $limit;
            $topRatedList = BOL_RateService::getInstance()->findMostRatedEntityList('photo_rates', $first, $limit, $exclude);

            if ( !$topRatedList )
            {
                return array();
            }
            
            $photoArr = $this->findPhotoInfoListByIdList(array_keys($topRatedList));

            $photos = array();

            foreach ( $photoArr as $key => $photo )
            {
                $photos[$key] = $photo;
                $photos[$key]['score'] = $topRatedList[$photo['id']]['avgScore'];
                $photos[$key]['rates'] = $topRatedList[$photo['id']]['ratesCount'];
            }

            usort($photos, array('PHOTO_BOL_PhotoService', 'sortArrayItemByDesc'));
        }
        else
        {
            $photos = $this->getPhotoList($type, $page, $limit, $checkPrivacy,$exclude);
        }
        
        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $photos[$key]['url'] = $this->getPhotoUrl($photo['id'], $photo['hash'], FALSE);
            }
        }

        return $photos;
    }

    public function findPhotosInAlbum( $albumId, array $photoIds = null )
    {
        if ( empty($albumId) )
        {
            return array();
        }

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                    ON(`p`.`albumId` = `a`.`id`)
            WHERE `p`.`albumId` = :albumId';

        if ( count($photoIds) !== 0 )
        {
            $sql .= ' AND `p`.`id` IN(' . $this->dbo->mergeInClause($photoIds) . ')';
        }

        return $this->dbo->queryForList($sql, array('albumId' => $albumId));
    }

    public function countPhotosInAlbumByPhotoIdList( $albumId, array $photoIdList )
    {
        if ( empty($albumId) )
        {
            return 0;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId AND 
                `id` IN (' . implode(',', array_map('intval', array_unique($photoIdList))) . ') AND
                `status` = :status';
        
        return (int)$this->dbo->queryForColumn($sql, array('albumId' => $albumId, 'status' => 'approved'));
    }
    
    public function countPhotosByUploadKey( $uploadKey )
    {
        if ( empty($uploadKey) )
        {
            return 0;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `uploadKey` = :key AND `status` = :status';
        
        return (int)$this->dbo->queryForColumn($sql, array('key' => $uploadKey, 'status' => 'approved'));
    }
    
    public function updateUploadKeyByPhotoIdList( array $photoIdList, $key )
    {
        if ( count($photoIdList) === 0 )
        {
            return 0;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `uploadKey` = :key
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($photoIdList))) . ')';
        
        return $this->dbo->query($sql, array('key' => $key));
    }
    
    public function findDistinctPhotoUploadKeyByAlbumId( $albumId )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('albumId' => $albumId));
    }
    
    public function findPhotoIdListByAlbumId( $albumId, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        
        $sql = ' SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId';
        
        if ( count($exclude) !== 0 )
        {
            $sql .= ' AND `id` NOT IN (' . implode(',', array_map('intval', array_unique($exclude))) . ')';
        }
        
        return $this->dbo->queryForColumnList($sql, array('albumId' => $albumId));
    }
    
    public function findPhotoIdListByUserIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByUsername', array('photo' => 'p', 'album' => 'a'));
        
        $sql = ' SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN ' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . ' AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `a`.`userId` IN(' . $this->dbo->mergeInClause($idList) . ') AND ' . $condition['where'];
        
        return $this->dbo->queryForColumnList($sql, $condition['params']);
    }

    // Content provider
    public function getPhotoListByIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            WHERE `p`.`id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForList($sql);
    }
}
