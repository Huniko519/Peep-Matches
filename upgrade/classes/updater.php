<?php

class Updater
{
    public static $storage = null;

    /**
     * @return PEEP_Database
     */
    public static function getDbo()
    {
        return PEEP::getDbo();
    }

    /**
     * @return UPDATE_LanguageService
     */
    public static function getLanguageService()
    {
        return UPDATE_LanguageService::getInstance();
    }

    /**
     * @return UPDATE_WidgetService
     */
    public static function getWidgetService()
    {
        return UPDATE_WidgetService::getInstance();
    }

    /**
     * @return UPDATE_ConfigService
     */
    public static function getConfigService()
    {
        return UPDATE_ConfigService::getInstance();
    }

    /**
     * @return UPDATE_NavigationService
     */
    public static function getNavigationService()
    {
        return UPDATE_NavigationService::getInstance();
    }

    /**
     * @return UPDATE_AuthorizationService
     */
    public static function getAuthorizationService()
    {
        return UPDATE_AuthorizationService::getInstance();
    }
    
    /**
     * @return UPDATE_Log
     */
    public static function getLogger()
    {
        return UPDATE_Log::getInstance();
    }

    /**
     * @return PEEP_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            switch ( true )
            {
                case defined('PEEP_USE_AMAZON_S3_CLOUDFILES') && PEEP_USE_AMAZON_S3_CLOUDFILES :
                    self::$storage = new UPDATE_AmazonCloudStorage();
                    break;

                /* case defined('PEEP_USE_CLOUDFILES') && PEEP_USE_CLOUDFILES :
                    self::$storage = new BASE_CLASS_CloudStorage();
                    break; */

                default :
                    self::$storage = new BASE_CLASS_FileStorage();
                    break;
            }
        }

        return self::$storage;
    }
}
