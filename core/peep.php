<?php

final class PEEP
{
    const CONTEXT_MOBILE = PEEP_Application::CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = PEEP_Application::CONTEXT_DESKTOP;
    const CONTEXT_API = PEEP_Application::CONTEXT_API;

    private static $context;

    private static function detectContext()
    {
        if ( self::$context !== null )
        {
            return;
        }

        if ( defined('PEEP_USE_CONTEXT') )
        {
            switch ( true )
            {
                case PEEP_USE_CONTEXT == 1:
                    self::$context = self::CONTEXT_DESKTOP;
                    return;

                case PEEP_USE_CONTEXT == 1 << 1:
                    self::$context = self::CONTEXT_MOBILE;
                    return;

                case PEEP_USE_CONTEXT == 1 << 2:
                    self::$context = self::CONTEXT_API;
                    return;
            }
        }

        $context = self::CONTEXT_DESKTOP;

        try
        {
            $isSmart = UTIL_Browser::isSmartphone();
        }
        catch ( Exception $e )
        {
            return;
        }

        if ( defined('PEEP_CRON') )
        {
            $context = self::CONTEXT_DESKTOP;
        }
        else if ( self::getSession()->isKeySet(PEEP_Application::CONTEXT_NAME) )
        {
            $context = self::getSession()->get(PEEP_Application::CONTEXT_NAME);
        }
        else if ( $isSmart )
        {
            $context = self::CONTEXT_MOBILE;
        }

        if ( defined('PEEP_USE_CONTEXT') )
        {
            if ( (PEEP_USE_CONTEXT & 1 << 1) == 0 && $context == self::CONTEXT_MOBILE )
            {
                $context = self::CONTEXT_DESKTOP;
            }

            if ( (PEEP_USE_CONTEXT & 1 << 2) == 0 && $context == self::CONTEXT_API )
            {
                $context = self::CONTEXT_DESKTOP;
            }
        }

        if ( (bool) PEEP::getConfig()->getValue('base', 'disable_mobile_context') && $context == self::CONTEXT_MOBILE )
        {
            $context = self::CONTEXT_DESKTOP;
        }


        //temp API context detection
        //TODO remake
        $uri = UTIL_Url::getRealRequestUri(PEEP::getRouter()->getBaseUrl(), $_SERVER['REQUEST_URI']);

        if ( mb_strstr($uri, '/') )
        {
            if ( trim(mb_substr($uri, 0, mb_strpos($uri, '/'))) == 'api' )
            {
                $context = self::CONTEXT_API;
            }
        }
        else
        {
            if ( trim($uri) == 'api' )
            {
                $context = self::CONTEXT_API;
            }
        }

        self::$context = $context;
    }

    /**
     * Returns autoloader object.
     *
     * @return PEEP_Autoload
     */
    public static function getAutoloader()
    {
        return PEEP_Autoload::getInstance();
    }

    /**
     * Returns front controller object.
     *
     * @return PEEP_Application
     */
    public static function getApplication()
    {
        self::detectContext();

        switch ( self::$context )
        {
            

            case self::CONTEXT_API:
                return PEEP_ApiApplication::getInstance();

            default:
                return PEEP_Application::getInstance();
        }
    }

    /**
     * Returns global config object.
     *
     * @return PEEP_Config
     */
    public static function getConfig()
    {
        return PEEP_Config::getInstance();
    }

    /**
     * Returns session object.
     *
     * @return PEEP_Session
     */
    public static function getSession()
    {
        return PEEP_Session::getInstance();
    }

    /**
     * Returns current web user object.
     *
     * @return PEEP_User
     */
    public static function getUser()
    {
        return PEEP_User::getInstance();
    }
    /**
     * Database object instance.
     *
     * @var PEEP_Database
     */
    private static $dboInstance;

    /**
     * Returns DB access object with default connection.
     *
     * @return PEEP_Database
     */
    public static function getDbo()
    {
        if ( self::$dboInstance === null )
        {
            $params = array(
                'host' => PEEP_DB_HOST,
                'username' => PEEP_DB_USER,
                'password' => PEEP_DB_PASSWORD,
                'dbname' => PEEP_DB_NAME
            );
            if ( defined('PEEP_DB_PORT') && (PEEP_DB_PORT !== null) )
            {
                $params['port'] = PEEP_DB_PORT;
            }
            if ( defined('PEEP_DB_SOCKET') )
            {
                $params['socket'] = PEEP_DB_SOCKET;
            }

            if ( PEEP_DEV_MODE || PEEP_PROFILER_ENABLE )
            {
                $params['profilerEnable'] = true;
            }

            if ( PEEP_DEBUG_MODE )
            {
                $params['debugMode'] = true;
            }

            self::$dboInstance = PEEP_Database::getInstance($params);
        }
        return self::$dboInstance;
    }

    /**
     * Returns system mailer object.
     *
     * 	@return PEEP_Mailer
     */
    public static function getMailer()
    {
        return PEEP_Mailer::getInstance();
    }

    /**
     * Returns responded HTML document object.
     *
     * @return PEEP_HtmlDocument
     */
    public static function getDocument()
    {
        return PEEP_Response::getInstance()->getDocument();
    }

    /**
     * Returns global request object.
     *
     * @return PEEP_Request
     */
    public static function getRequest()
    {
        return PEEP_Request::getInstance();
    }

    /**
     * Returns global response object.
     *
     * @return PEEP_Response
     */
    public static function getResponse()
    {
        return PEEP_Response::getInstance();
    }

    /**
     * Returns language object.
     *
     * @return PEEP_Language
     */
    public static function getLanguage()
    {
        return PEEP_Language::getInstance();
    }

    /**
     * Returns system router object.
     *
     * @return PEEP_Router
     */
    public static function getRouter()
    {
        return PEEP_Router::getInstance();
    }

    /**
     * Returns system plugin manager object.
     *
     * @return PEEP_PluginManager
     */
    public static function getPluginManager()
    {
        return PEEP_PluginManager::getInstance();
    }

    /**
     * Returns system theme manager object.
     *
     * @return PEEP_ThemeManager
     */
    public static function getThemeManager()
    {
        return PEEP_ThemeManager::getInstance();
    }

    /**
     * Returns system event manager object.
     *
     * @return PEEP_EventManager
     */
    public static function getEventManager()
    {
        return PEEP_EventManager::getInstance();
    }

    /**
     * @return PEEP_Registry
     */
    public static function getRegistry()
    {
        return PEEP_Registry::getInstance();
    }

    /**
     * Returns global feedback object.
     *
     * @return PEEP_Feedback
     */
    public static function getFeedback()
    {
        return PEEP_Feedback::getInstance();
    }

    /**
     * Returns global navigation object.
     *
     * @return PEEP_Navigation
     */
    public static function getNavigation()
    {
        return PEEP_Navigation::getInstance();
    }

    /**
     * @deprecated
     * @return PEEP_Dispatcher
     */
    public static function getDispatcher()
    {
        return PEEP_RequestHandler::getInstance();
    }

    /**
     * @return PEEP_RequestHandler
     */
    public static function getRequestHandler()
    {
        self::detectContext();

        switch ( self::$context )
        {
            case self::CONTEXT_API:
                return PEEP_ApiRequestHandler::getInstance();

            default:
                return PEEP_RequestHandler::getInstance();
        }
    }

    /**
     *
     * @return PEEP_CacheService
     */
    public static function getCacheService()
    {
        return BOL_DbCacheService::getInstance(); //TODO make configurable
    }
    private static $storage;

    /**
     *
     * @return PEEP_Storage
     */
    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            self::$storage = PEEP::getEventManager()->call('core.get_storage');

            if ( self::$storage === null )
            {
                switch ( true )
                {
                    case defined('PEEP_USE_AMAZON_S3_CLOUDFILES') && PEEP_USE_AMAZON_S3_CLOUDFILES :
                        self::$storage = new BASE_CLASS_AmazonCloudStorage();
                        break;

                    case defined('PEEP_USE_CLOUDFILES') && PEEP_USE_CLOUDFILES :
                        self::$storage = new BASE_CLASS_CloudStorage();
                        break;

                    default :
                        self::$storage = new BASE_CLASS_FileStorage();
                        break;
                }
            }
        }

        return self::$storage;
    }

    public static function getLogger( $logType = 'peep' )
    {
        return PEEP_Log::getInstance($logType);
    }

    /**
     * @return PEEP_Authorization
     */
    public static function getAuthorization()
    {
        return PEEP_Authorization::getInstance();
    }

    /**
     * @return PEEP_CacheManager
     */
    public static function getCacheManager()
    {
        return PEEP_CacheManager::getInstance();
    }

    public static function getClassInstance( $className, $arguments = null )
    {
        $args = func_get_args();
        $constuctorArgs = array_splice($args, 1);

        return self::getClassInstanceArray($className, $constuctorArgs);
    }

    public static function getClassInstanceArray( $className, array $arguments = array() )
    {
        $params = array(
            'className' => $className,
            'arguments' => $arguments
        );

        $eventManager = PEEP::getEventManager();
        $eventManager->trigger(new PEEP_Event("core.performance_test", array("key" => "component_construct.start", "params" => $params)));

        $event = new PEEP_Event("class.get_instance." . $className, $params);
        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new PEEP_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $event = new PEEP_Event("class.get_instance", $params);

        $eventManager->trigger($event);
        $instance = $event->getData();

        if ( $instance !== null )
        {
            $eventManager->trigger(new PEEP_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
            return $instance;
        }

        $rClass = new ReflectionClass($className);
        $eventManager->trigger(new PEEP_Event("core.performance_test", array("key" => "component_construct.end", "params" => $params)));
        return $rClass->newInstanceArgs($arguments);
    }

    /**
     * Returns text search manager object.
     *
     * @return PEEP_TextSearchManager
     */
    public static function getTextSearchManager()
    {
        return PEEP_TextSearchManager::getInstance();
    }
}
