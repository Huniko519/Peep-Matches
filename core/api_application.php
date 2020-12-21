<?php

class PEEP_ApiApplication extends PEEP_Application
{

    private function __construct()
    {
        $this->context = self::CONTEXT_API;
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_ApiApplication
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_ApiApplication
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Application init actions.
     */
    public function init()
    {
        require_once PEEP_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'json_err_output.php';
        PEEP_ErrorManager::getInstance()->setErrorOutput(new BASE_CLASS_JsonErrOutput());

        $authToken = empty($_SERVER["HTTP_API_AUTH_TOKEN"]) ? null : $_SERVER["HTTP_API_AUTH_TOKEN"];
        PEEP_Auth::getInstance()->setAuthenticator(new PEEP_TokenAuthenticator($authToken));
        
        if ( !empty($_SERVER["HTTP_API_LANGUAGE"]) )
        {
            $languageDto = BOL_LanguageService::getInstance()->findByTag($_SERVER["HTTP_API_LANGUAGE"]);
            
            if ( !empty($languageDto) && $languageDto->status == "active" )
            {
                BOL_LanguageService::getInstance()->setCurrentLanguage($languageDto);
            }
        }
        
        // setting default time zone
        date_default_timezone_set(PEEP::getConfig()->getValue('base', 'site_timezone'));

//        PEEP::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
//        PEEP::getRequestHandler()->setStaticPageAttributes('BASE_CTRL_StaticDocument');
//
//        // router init - need to set current page uri and base url
        $router = PEEP::getRouter();
        $router->setBaseUrl(PEEP_URL_HOME . 'api/');
        $uri = PEEP::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);

        $router->setDefaultRoute(new PEEP_ApiDefaultRoute());

        PEEP::getPluginManager()->initPlugins();
        $event = new PEEP_Event(PEEP_EventManager::ON_PLUGINS_INIT);
        PEEP::getEventManager()->trigger($event);

        $beckend = PEEP::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            PEEP::getCacheManager()->setCacheBackend($beckend);
            PEEP::getCacheManager()->setLifetime(3600);
            PEEP::getDbo()->setUseCashe(true);
        }

        PEEP::getResponse()->setDocument($this->newDocument());

        if ( PEEP::getUser()->isAuthenticated() )
        {
            BOL_UserService::getInstance()->updateActivityStamp(PEEP::getUser()->getId(), $this->getContext());
        }
    }

    /**
     * Finds controller and action for current request.
     */
    public function route()
    {
        try
        {
            PEEP::getRequestHandler()->setHandlerAttributes(PEEP::getRouter()->route());
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            PEEP::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
        }
    }

    /**
     * ---------
     */
    public function handleRequest()
    {
        try
        {
            PEEP::getRequestHandler()->dispatch();
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            PEEP::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
            $this->handleRequest();
        }
        catch ( Exception $e )
        {
            $errorType = "exception";
            
            $responseData = array(
                "exception" => get_class($e),
                "message" => $e->getMessage(),
                "code" => $e->getCode()
            );
            
            if ( $e instanceof ApiResponseErrorException )
            {
                $responseData["userData"] = $e->data;
                $errorType = "userError";
            }
            else if ( defined("PEEP_DEBUG_MODE") && PEEP_DEBUG_MODE )
            {
                $responseData["trace"] = $e->getTraceAsString();
            }
            
            $apiResponse = array(
                "type" => $errorType,
                "data" => $responseData
            );
            
            //PEEP::getResponse()->setHeader(PEEP_Response::HD_CNT_TYPE, "application/json");
            //PEEP::getDocument()->setBody($apiResponse);
            
            header('Content-Type: application/json');
            
            echo json_encode($apiResponse);
            exit; // TODO remove exit
        }
    }

    /**
     * Method called just before request responding.
     */
    public function finalize()
    {
//        $document = PEEP::getDocument();
//
//        $meassages = PEEP::getFeedback()->getFeedback();
//
//        foreach ( $meassages as $messageType => $messageList )
//        {
//            foreach ( $messageList as $message )
//            {
//                $document->addOnloadScript("PEEP.message(" . json_encode($message) . ", '" . $messageType . "');");
//            }
//        }

        $event = new PEEP_Event(PEEP_EventManager::ON_FINALIZE);
        PEEP::getEventManager()->trigger($event);
    }

    /**
     * System method. Don't call it!!!
     */
    public function onBeforeDocumentRender()
    {
//        $document = PEEP::getDocument();
//
//        $document->addStyleSheet(PEEP::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'default.css' . '?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -100);
//        $document->addStyleSheet(PEEP::getThemeManager()->getCssFileUrl() . '?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', (-90));
//
//        // add custom css if page is not admin TODO replace with another condition
//        if ( !PEEP::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
//        {
//            if ( PEEP::getThemeManager()->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
//            {
//                $document->addStyleSheet(PEEP::getThemeManager()->getThemeService()->getCustomCssFileUrl(PEEP::getThemeManager()->getCurrentTheme()->getDto()->getName()));
//            }
//
//            if ( $this->getDocumentKey() !== 'base.sign_in' )
//            {
//                $customHeadCode = PEEP::getConfig()->getValue('base', 'html_head_code');
//                $customAppendCode = PEEP::getConfig()->getValue('base', 'html_prebody_code');
//
//                if ( !empty($customHeadCode) )
//                {
//                    $document->addCustomHeadInfo($customHeadCode);
//                }
//
//                if ( !empty($customAppendCode) )
//                {
//                    $document->appendBody($customAppendCode);
//                }
//            }
//        }
//
//        $language = PEEP::getLanguage();
//
//        if ( $document->getTitle() === null )
//        {
//            $document->setTitle($language->text('nav', 'page_default_title'));
//        }
//
//        if ( $document->getDescription() === null )
//        {
//            $document->setDescription($language->text('nav', 'page_default_description'));
//        }
//
//        /* if ( $document->getKeywords() === null )
//          {
//          $document->setKeywords($language->text('nav', 'page_default_keywords'));
//          } */
//
//        if ( $document->getHeadingIconClass() === null )
//        {
//            $document->setHeadingIconClass('peep_ic_file');
//        }
//
//        if ( !empty($this->documentKey) )
//        {
//            $document->setBodyClass($this->documentKey);
//        }
//
//        if ( $this->getDocumentKey() !== null )
//        {
//            $masterPagePath = PEEP::getThemeManager()->getDocumentMasterPage($this->getDocumentKey());
//
//            if ( $masterPagePath !== null )
//            {
//                $document->getMasterPage()->setTemplate($masterPagePath);
//            }
//        }
    }

    /**
     * Triggers response object to send rendered page.
     */
    public function returnResponse()
    {
        PEEP::getResponse()->respond();
    }

    /**
     * Makes header redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null, $switchContextTo = false )
    {
//        if ( $switchContextTo !== false && in_array($switchContextTo, array(self::CONTEXT_DESKTOP, self::CONTEXT_MOBILE)) )
//        {
//            PEEP::getSession()->set(self::CONTEXT_NAME, $switchContextTo);
//        }
//
//        // if empty redirect location -> current URI is used
//        if ( $redirectTo === null )
//        {
//            $redirectTo = PEEP::getRequest()->getRequestUri();
//        }
//
//        // if URI is provided need to add site home URL
//        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
//        {
//            $redirectTo = PEEP::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
//        }
//
//        UTIL_Url::redirect($redirectTo);
    }

    /**
     * Menu item to activate.
     *
     * @var BOL_MenuItem
     */
    public function activateMenuItem()
    {
//        if ( !PEEP::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
//        {
//            if ( PEEP::getRequest()->getRequestUri() === '/' || PEEP::getRequest()->getRequestUri() === '' )
//            {
//                PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, $this->indexMenuItem->getPrefix(), $this->indexMenuItem->getKey());
//            }
//        }
    }
    /* private auxilary methods */

    protected function newDocument()
    {
        $document = new PEEP_ApiDocument();

        return $document;

//        $language = BOL_LanguageService::getInstance()->getCurrent();
//        $document = new PEEP_HtmlDocument();
//        $document->setCharset('UTF-8');
//        $document->setMime('text/html');
//        $document->setLanguage($language->getTag());
//
//        if ( $language->getRtl() )
//        {
//            $document->setDirection('rtl');
//        }
//        else
//        {
//            $document->setDirection('ltr');
//        }
//
//        if ( (bool) PEEP::getConfig()->getValue('base', 'favicon') )
//        {
//            $document->setFavicon(PEEP::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico');
//        }
//
//        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
//        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));
//
//        //$document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'json2.js', 'text/javascript', (-99));
//        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'default.js?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'text/javascript', (-50));
//
//        $onloadJs = "PEEP.bindAutoClicks();PEEP.bindTips($('body'));";
//
//        if ( PEEP::getUser()->isAuthenticated() )
//        {
//            $activityUrl = PEEP::getRouter()->urlFor('BASE_CTRL_User', 'updateActivity');
//            $onloadJs .= "PEEP.getPing().addCommand('user_activity_update').start(600000);";
//        }
//
//        $document->addOnloadScript($onloadJs);
//        PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));

        return $document;
    }

    protected function addCatchAllRequestsException( $eventName, $key )
    {
        $event = new BASE_CLASS_EventCollector($eventName);
        PEEP::getEventManager()->trigger($event);
        $exceptions = $event->getData();

        foreach ( $exceptions as $item )
        {
            if ( is_array($item) && !empty($item['controller']) && !empty($item['action']) )
            {
                PEEP::getRequestHandler()->addCatchAllRequestsExclude($key, trim($item['controller']), trim($item['action']));
            }
        }
    }
}
