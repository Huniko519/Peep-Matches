<?php

class PHOTO_Cron extends PEEP_Cron
{
    const ALBUMS_DELETE_LIMIT = 10;
    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('albumsDeleteProcess');
        $this->addJob('contentIndexing');
        $this->addJob('cleareCache', 10);
        $this->addJob('deleteLimitedPhotos', 180);
        $this->addJob('updatePhotoTags');
    }

    public function run()
    {
        
    }

    public function albumsDeleteProcess()
    {
        $config = PEEP::getConfig();
        
        // check if uninstall is in progress
        if ( !$config->getValue('photo', 'uninstall_inprogress') )
        {
            return;
        }
        
        // check if cron queue is not busy
        if ( $config->getValue('photo', 'uninstall_cron_busy') )
        {
            return;
        }
        
        $config->saveConfig('photo', 'uninstall_cron_busy', 1);
        
        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        
        try
        {
            $albumService->deleteAlbums(self::ALBUMS_DELETE_LIMIT);
        }
        catch ( Exception $e )
        {
            PEEP::getLogger()->addEntry(json_encode($e));
        }

        $config->saveConfig('photo', 'uninstall_cron_busy', 0);
        
        if ( !$albumService->countAlbums() ) 
        {
            BOL_PluginService::getInstance()->uninstall('photo');
            $config->saveConfig('photo', 'uninstall_inprogress', 0);

            PHOTO_BOL_PhotoService::getInstance()->setMaintenanceMode(false);
        }
    }
    
    public function cleareCache()
    {
        PHOTO_BOL_PhotoCacheDao::getInstance()->cleareCache();
    }
    
    public function deleteLimitedPhotos()
    {
        PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteLimitedPhotos();
    }

    public function contentIndexing()
    {
        PHOTO_BOL_SearchService::getInstance()->contentIndexing();
    }

    public function updatePhotoTags()
    {
        if ( PEEP::getConfig()->getValue('photo', 'update_tag_process') )
        {
            $sql = 'SELECT `et`.`id`, `et`.`entityId`, `et`.`tagId`
                FROM `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `et`
                    INNER JOIN `'. PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`et`.`entityId` = `p`.`id`)
                WHERE `et`.`entityType` = :entityType AND
                    `et`.`id` NOT IN (SELECT `entityTagId` FROM `' . PEEP_DB_PREFIX . 'photo_update_tag`) AND
                    `p`.`dimension` IS NULL
                LIMIT :limit';

            $tagList = PEEP::getDbo()->queryForList($sql, array('entityType' => 'photo', 'limit' => 500));

            if ( empty($tagList) )
            {
                PEEP::getConfig()->saveConfig('photo', 'update_tag_process', false);

                return;
            }

            $photoTagList = array();
            $tagIdList = array();

            foreach ( $tagList as $tag )
            {
                if ( !array_key_exists($tag['entityId'], $photoTagList) )
                {
                    $photoTagList[$tag['entityId']] = array();
                }

                $photoTagList[$tag['entityId']][] = $tag['tagId'];
                $tagIdList[] = $tag['id'];
            }

            foreach ( $photoTagList as $photoId => $photoTag )
            {
                $tags = BOL_TagDao::getInstance()->findByIdList($photoTag);

                if ( empty($tags) )
                {
                    continue;
                }

                $str = array();

                foreach ( $tags as $tag )
                {
                    $str[] = '#' . implode('', array_map('trim', explode(' ', $tag->label)));
                }

                $photo = PHOTO_BOL_PhotoDao::getInstance()->findById($photoId);
                $photo->description .= ' ' . implode(' ', $str);
                PHOTO_BOL_PhotoDao::getInstance()->save($photo);
            }

            PEEP::getDbo()->query('INSERT IGNORE INTO `' . PEEP_DB_PREFIX . 'photo_update_tag`(`entityTagId`) VALUES(' . implode('),(', $tagIdList) . ');')  ;
        }
    }
}
