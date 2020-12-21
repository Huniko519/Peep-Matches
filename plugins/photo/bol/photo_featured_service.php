<?php

final class PHOTO_BOL_PhotoFeaturedService
{
    /**
     * @var PHOTO_BOL_PhotofeaturedDao
     */
    private $photoFeaturedDao;
    /**
     * Class instance
     *
     * @var PHOTO_BOL_PhotoFeaturedService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PHOTO_BOL_PhotofeaturedService
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
     * Check if photo is featured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function isFeatured( $photoId )
    {
        return $this->photoFeaturedDao->isFeatured($photoId);
    }

    /**
     * Marks photo as featured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function markFeatured( $photoId )
    {
        $marked = $this->photoFeaturedDao->markFeatured($photoId);
        
        if ( $marked ) 
        {
            PHOTO_BOL_PhotoService::getInstance()->cleanListCache();
        }

        $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_EDIT, array('photoId' => $photoId));
        PEEP::getEventManager()->trigger($event);
        
        return $marked;
    }

    /**
     * Marks photo as unfeatured
     * 
     * @param int $photoId
     * @return boolean
     */
    public function markUnfeatured( $photoId )
    {
        $marked = $this->photoFeaturedDao->markUnfeatured($photoId);
        
        if ( $marked ) 
        {
            PHOTO_BOL_PhotoService::getInstance()->cleanListCache();
        }

        $event = new PEEP_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_EDIT, array('photoId' => $photoId));
        PEEP::getEventManager()->trigger($event);
        
        return $marked;
    }
}