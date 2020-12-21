<?php

class PEEP_Application
{
    const CONTEXT_MOBILE = BOL_UserService::USER_CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = BOL_UserService::USER_CONTEXT_DESKTOP;
    const CONTEXT_API = BOL_UserService::USER_CONTEXT_API;
    const CONTEXT_NAME = 'peepContext';

    /**
     * Current page document key.
     *
     * @var string
     */
    protected $documentKey;

    /**
     * @var string
     */
    protected $context;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->context = self::CONTEXT_DESKTOP;
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Application
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Application
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
     * Sets site maintenance mode.
     *
     * @param boolean $mode
     */
    public function setMaintenanceMode( $mode )
    {
        PEEP::getConfig()->saveConfig('base', 'maintenance', (bool) $mode);
    }

    /**
     * @return string
     */
    public function getDocumentKey()
    {
        return $this->documentKey;
    }

    /**
     * @param string $key
     */
    public function setDocumentKey( $key )
    {
        $this->documentKey = $key;
    }

    /**
     * Application init actions.
     */
    public function init()
    {
        // router init - need to set current page uri and base url
        $router = PEEP::getRouter();
        $this->urlHostRedirect();
        PEEP_Auth::getInstance()->setAuthenticator(new PEEP_SessionAuthenticator());
        $this->userAutoLogin();

        // setting default time zone
        date_default_timezone_set(PEEP::getConfig()->getValue('base', 'site_timezone'));

        PEEP::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
        PEEP::getRequestHandler()->setStaticPageAttributes('BASE_CTRL_StaticDocument');
        $uri = PEEP::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);

        $defaultRoute = new PEEP_DefaultRoute();
        //$defaultRoute->setControllerNamePrefix('CTRL');
        $router->setDefaultRoute($defaultRoute);

        PEEP::getPluginManager()->initPlugins();
        $event = new PEEP_Event(PEEP_EventManager::ON_PLUGINS_INIT);
        PEEP::getEventManager()->trigger($event);

        $navService = BOL_NavigationService::getInstance();

        // try to find static document with current uri
        $document = $navService->findStaticDocument($uri);

        if ( $document !== null )
        {
            $this->documentKey = $document->getKey();
        }

        $beckend = PEEP::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            PEEP::getCacheManager()->setCacheBackend($beckend);
            PEEP::getCacheManager()->setLifetime(3600);
            PEEP::getDbo()->setUseCashe(true);
        }

        $this->devActions();

        PEEP::getThemeManager()->initDefaultTheme();

        // setting current theme
        $activeThemeName = PEEP::getEventManager()->call('base.get_active_theme_name');
        $activeThemeName = $activeThemeName ? $activeThemeName : PEEP::getConfig()->getValue('base', 'selectedTheme');

        if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && PEEP::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
        {
            PEEP_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(trim($activeThemeName)));
        }

        // adding static document routes
        $staticDocs = $navService->findAllStaticDocuments();
        $staticPageDispatchAttrs = PEEP::getRequestHandler()->getStaticPageAttributes();

        /* @var $value BOL_Document */
        foreach ( $staticDocs as $value )
        {
            PEEP::getRouter()->addRoute(new PEEP_Route($value->getKey(), $value->getUri(), $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => $value->getKey()))));

            // TODO refactor - hotfix for TOS page
            if ( UTIL_String::removeFirstAndLastSlashes($value->getUri()) == 'terms-of-use' )
            {
                PEEP::getRequestHandler()->addCatchAllRequestsExclude('base.members_only', $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'], array('documentKey' => $value->getKey()));
            }
        }

        //adding index page route
        $item = BOL_NavigationService::getInstance()->findFirstLocal((PEEP::getUser()->isAuthenticated() ? BOL_NavigationService::VISIBLE_FOR_MEMBER : BOL_NavigationService::VISIBLE_FOR_GUEST), PEEP_Navigation::MAIN);

        if ( $item !== null )
        {
            if ( $item->getRoutePath() )
            {
                $route = PEEP::getRouter()->getRoute($item->getRoutePath());
                $ddispatchAttrs = $route->getDispatchAttrs();
            }
            else
            {
                $ddispatchAttrs = PEEP::getRequestHandler()->getStaticPageAttributes();
            }

            $router->addRoute(new PEEP_Route('base_default_index', '/', $ddispatchAttrs['controller'], $ddispatchAttrs['action'], array('documentKey' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => $item->getDocumentKey()))));
            $this->indexMenuItem = $item;
            PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'activateMenuItem'));
        }
        else
        {
            $router->addRoute(new PEEP_Route('base_default_index', '/', 'BASE_CTRL_ComponentPanel', 'index'));
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getResponse()->setDocument($this->newDocument());
            PEEP::getDocument()->setMasterPage(new PEEP_MasterPage());
            PEEP::getResponse()->setHeader(PEEP_Response::HD_CNT_TYPE, PEEP::getDocument()->getMime() . '; charset=' . PEEP::getDocument()->getCharset());
        }
        else
        {
            PEEP::getResponse()->setDocument(new PEEP_AjaxDocument());
        }

        /* additional actions */
        if ( PEEP::getUser()->isAuthenticated() )
        {
            BOL_UserService::getInstance()->updateActivityStamp(PEEP::getUser()->getId(), $this->getContext());
        }

        // adding global template vars
        $currentThemeImagesDir = PEEP::getThemeManager()->getCurrentTheme()->getStaticImagesUrl();
        $viewRenderer = PEEP_ViewRenderer::getInstance();
        $viewRenderer->assignVar('themeImagesUrl', $currentThemeImagesDir);
        $viewRenderer->assignVar('siteName', PEEP::getConfig()->getValue('base', 'site_name'));
        $viewRenderer->assignVar('siteTagline', PEEP::getConfig()->getValue('base', 'site_tagline'));
        $viewRenderer->assignVar('siteUrl', PEEP_URL_HOME);
        
        if ( function_exists('peep_service_actions') )
        {
            call_user_func('peep_service_actions');
        }

        $this->handleHttps();
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

        $this->httpVsHttpsRedirect();
    }

    /**
     * ---------
     */
    public function handleRequest()
    {
        $baseConfigs = PEEP::getConfig()->getValues('base');

        //members only
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW && !PEEP::getUser()->isAuthenticated() )
        {
            $attributes = array(
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_User',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'standardSignIn'
            );

            PEEP::getRequestHandler()->setCatchAllRequestsAttributes('base.members_only', $attributes);
            $this->addCatchAllRequestsException('base.members_only_exceptions', 'base.members_only');
        }

        //splash screen
        if ( (bool) PEEP::getConfig()->getValue('base', 'splash_screen') && !isset($_COOKIE['splashScreen']) )
        {
            $attributes = array(
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'splashScreen',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true,
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_JS => true,
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_ROUTE => 'base_page_splash_screen'
            );

            PEEP::getRequestHandler()->setCatchAllRequestsAttributes('base.splash_screen', $attributes);
            $this->addCatchAllRequestsException('base.splash_screen_exceptions', 'base.splash_screen');
        }

        // password protected
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_PASSWORD_VIEW && !PEEP::getUser()->isAuthenticated() && !isset($_COOKIE['base_password_protection'])
        )
        {
            $attributes = array(
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'passwordProtection'
            );

            PEEP::getRequestHandler()->setCatchAllRequestsAttributes('base.password_protected', $attributes);
            $this->addCatchAllRequestsException('base.password_protected_exceptions', 'base.password_protected');
        }

        // maintenance mode
        if ( (bool) $baseConfigs['maintenance'] && !PEEP::getUser()->isAdmin() )
        {
            $attributes = array(
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'maintenance',
                PEEP_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true
            );

            PEEP::getRequestHandler()->setCatchAllRequestsAttributes('base.maintenance_mode', $attributes);
            $this->addCatchAllRequestsException('base.maintenance_mode_exceptions', 'base.maintenance_mode');
        }

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
    }

    /**
     * Method called just before request responding.
     */
    public function finalize()
    {
        $document = PEEP::getDocument();

        $meassages = PEEP::getFeedback()->getFeedback();

        foreach ( $meassages as $messageType => $messageList )
        {
            foreach ( $messageList as $message )
            {
                $document->addOnloadScript("PEEP.message(" . json_encode($message) . ", '" . $messageType . "');");
            }
        }

        $event = new PEEP_Event(PEEP_EventManager::ON_FINALIZE);
        PEEP::getEventManager()->trigger($event);
    }

    /**
     * System method. Don't call it!!!
     */
    public function onBeforeDocumentRender()
    {
        $document = PEEP::getDocument();

        $document->addStyleSheet(PEEP::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'default.css' . '?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -100);
        $document->addStyleSheet(PEEP::getThemeManager()->getCssFileUrl() . '?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', (-90));

        // add custom css if page is not admin TODO replace with another condition
        if ( !PEEP::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( PEEP::getThemeManager()->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
            {
                $document->addStyleSheet(PEEP::getThemeManager()->getThemeService()->getCustomCssFileUrl(PEEP::getThemeManager()->getCurrentTheme()->getDto()->getName()));
            }

            if ( $this->getDocumentKey() !== 'base.sign_in' )
            {
                $customHeadCode = PEEP::getConfig()->getValue('base', 'html_head_code');
                $customAppendCode = PEEP::getConfig()->getValue('base', 'html_prebody_code');

                if ( !empty($customHeadCode) )
                {
                    $document->addCustomHeadInfo($customHeadCode);
                }

                if ( !empty($customAppendCode) )
                {
                    $document->appendBody($customAppendCode);
                }
            }
        }
        else
        {
            $document->addStyleSheet(PEEP::getPluginManager()->getPlugin('admin')->getStaticCssUrl() . 'admin.css' . '?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -50);
        }

        $language = PEEP::getLanguage();

        if ( $document->getTitle() === null )
        {
            $document->setTitle($language->text('nav', 'page_default_title'));
        }

        if ( $document->getDescription() === null )
        {
            $document->setDescription($language->text('nav', 'page_default_description'));
        }

        /* if ( $document->getKeywords() === null )
          {
          $document->setKeywords($language->text('nav', 'page_default_keywords'));
          } */

        if ( $document->getHeadingIconClass() === null )
        {
            $document->setHeadingIconClass('peep_ic_file');
        }

        if ( !empty($this->documentKey) )
        {
            $document->setBodyClass($this->documentKey);
        }

        if ( $this->getDocumentKey() !== null )
        {
            $masterPagePath = PEEP::getThemeManager()->getDocumentMasterPage($this->getDocumentKey());

            if ( $masterPagePath !== null )
            {
                $document->getMasterPage()->setTemplate($masterPagePath);
            }
        }
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
        if ( $switchContextTo !== false && in_array($switchContextTo, array(self::CONTEXT_DESKTOP, self::CONTEXT_MOBILE)) )
        {
            PEEP::getSession()->set(self::CONTEXT_NAME, $switchContextTo);
        }

        // if empty redirect location -> current URI is used
        if ( $redirectTo === null )
        {
            $redirectTo = PEEP::getRequest()->getRequestUri();
        }

        // if URI is provided need to add site home URL
        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
        {
            $redirectTo = PEEP::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
        }

        UTIL_Url::redirect($redirectTo);
    }
    /**
     * Menu item to activate.
     *
     * @var BOL_MenuItem
     */
    private $indexMenuItem;

    public function activateMenuItem()
    {
        if ( !PEEP::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( PEEP::getRequest()->getRequestUri() === '/' || PEEP::getRequest()->getRequestUri() === '' )
            {
                PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, $this->indexMenuItem->getPrefix(), $this->indexMenuItem->getKey());
            }
        }
    }
    /* private auxilary methods */

    protected function newDocument()
    {
        $language = BOL_LanguageService::getInstance()->getCurrent();
        $document = new PEEP_HtmlDocument();
        $document->setCharset('UTF-8');
        $document->setMime('text/html');
        $document->setLanguage($language->getTag());

        if ( $language->getRtl() )
        {
            $document->setDirection('rtl');
        }
        else
        {
            $document->setDirection('ltr');
        }

        if ( (bool) PEEP::getConfig()->getValue('base', 'favicon') )
        {
            $document->setFavicon(PEEP::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico');
        }

        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));

        //$document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'json2.js', 'text/javascript', (-99));
        $document->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'default.js?' . PEEP::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'text/javascript', (-50));

        $onloadJs = "PEEP.bindAutoClicks();PEEP.bindTips($('body'));";

        if ( PEEP::getUser()->isAuthenticated() )
        {
            $activityUrl = PEEP::getRouter()->urlFor('BASE_CTRL_User', 'updateActivity');
            $onloadJs .= "PEEP.getPing().addCommand('user_activity_update').start(600000);";
        }

        $document->addOnloadScript($onloadJs);
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));

        return $document;
    }

    protected function devActions()
    {
//        if ( isset($_GET['capc']) && function_exists('apc_clear_cache') )
//        {
//            apc_clear_cache();
//            $this->redirect();
//        }

        if ( PEEP::getRequest()->isAjax() )
        {
            return;
        }

        if ( PEEP::getUser()->isAdmin() )
        {
            //TODO add clear smarty cache
            //TODO add clear themes cache
            //TODO add clear db cache
        }

        $configDev = (int) PEEP::getConfig()->getValue('base', 'dev_mode');

        if ( $configDev > 0 )
        {
            $this->updateCachedEntities($configDev);
            PEEP::getConfig()->saveConfig('base', 'dev_mode', 0);
            $this->redirect();
        }

        if ( PEEP_PROFILER_ENABLE )
        {
            //get data for developer tool
            PEEP_Renderable::setDevMode(true);
            PEEP::getEventManager()->setDevMode(true);

            function base_dev_tool( BASE_CLASS_EventCollector $event )
            {
                $viewRenderer = PEEP_ViewRenderer::getInstance();
                $prevVars = $viewRenderer->getAllAssignedVars();
                $viewRenderer->assignVar('peepdev', (array) (simplexml_load_file(PEEP_DIR_ROOT . 'soft-version.xml')));
                $requestHandlerData = PEEP::getRequestHandler()->getDispatchAttributes();

                try
                {
                    $ctrlPath = PEEP::getAutoloader()->getClassPath($requestHandlerData['controller']);
                }
                catch ( Exception $e )
                {
                    $ctrlPath = 'not_found';
                }

                $requestHandlerData['ctrlPath'] = $ctrlPath;
                $requestHandlerData['paramsExp'] = var_export(( empty($requestHandlerData['params']) ? array() : $requestHandlerData['params']), true);
                $viewRenderer->assignVar('requestHandler', $requestHandlerData);
                $viewRenderer->assignVar('profiler', UTIL_Profiler::getInstance()->getResult());
                $viewRenderer->assignVar('memoryUsage', (function_exists('memory_get_peak_usage') ? sprintf('%0.3f', memory_get_peak_usage(true) / 1048576) : 'No info'));

                if ( !PEEP_DEV_MODE || true )
                { //TODO remove hardcode
                    $viewRenderer->assignVar('clrBtnUrl', PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor('BASE_CTRL_Base', 'turnDevModeOn'), array('back-uri' => urlencode(PEEP::getRouter()->getUri()))));
                }

                $rndItems = PEEP_Renderable::getRenderedClasses();
                $rndArray = array('mp' => array(), 'cmp' => array(), 'ctrl' => array());
                foreach ( $rndItems as $key => $item )
                {
                    try
                    {
                        $src = PEEP::getAutoloader()->getClassPath($key);
                    }
                    catch ( Exception $e )
                    {
                        $src = 'not_found';
                    }

                    $addItem = array('class' => $key, 'src' => $src, 'tpl' => $item);

                    if ( strstr($key, 'PEEP_MasterPage') )
                    {
                        $rndArray['mp'] = $addItem;
                    }
                    else if ( strstr($key, '_CTRL_') )
                    {
                        $rndArray['ctrl'] = $addItem;
                    }
                    else
                    {
                        $rndArray['cmp'][] = $addItem;
                    }
                }

                $viewRenderer->assignVar('renderedItems', array('items' => $rndArray, 'count' => ( count(PEEP_Renderable::getRenderedClasses()) - 2 )));

                $queryLog = PEEP::getDbo()->getQueryLog();
                foreach ( $queryLog as $key => $query )
                {
                    if ( isset($_GET['pr_query_log_filter']) && strlen($_GET['pr_query_log_filter']) > 3 )
                    {
                        if ( !strstr($query['query'], $_GET['pr_query_log_filter']) )
                        {
                            unset($queryLog[$key]);
                            continue;
                        }
                    }

                    if ( isset($query['params']) && is_array($query['params']) )
                    {
                        $queryLog[$key]['params'] = var_export($query['params'], true);
                    }
                }

                $viewRenderer->assignVar('database', array('qet' => PEEP::getDbo()->getTotalQueryExecTime(), 'ql' => $queryLog, 'qc' => count($queryLog)));

                //events
                $eventsData = PEEP::getEventManager()->getLog();
                $eventsDataToAssign = array('bind' => array(), 'calls' => array());

                foreach ( $eventsData['bind'] as $eventName => $listeners )
                {
                    $listenersList = array();

                    foreach ( $listeners as $priority )
                    {
                        foreach ( $priority as $listener )
                        {
                            if ( is_array($listener) )
                            {
                                if ( is_object($listener[0]) )
                                {
                                    $listener = get_class($listener[0]) . ' -> ' . $listener[1];
                                }
                                else
                                {
                                    $listener = $listener[0] . ' :: ' . $listener[1];
                                }
                            }
                            else if ( is_string($listener) )
                            {
                                
                            }
                            else
                            {
                                $listener = 'ClosureObject';
                            }

                            $listenersList[] = $listener;
                        }
                    }

                    $eventsDataToAssign['bind'][] = array('name' => $eventName, 'listeners' => $listenersList);
                }

                foreach ( $eventsData['call'] as $eventItem )
                {
                    $listenersList = array();

                    foreach ( $eventItem['listeners'] as $priority )
                    {
                        foreach ( $priority as $listener )
                        {
                            if ( is_array($listener) )
                            {
                                if ( is_object($listener[0]) )
                                {
                                    $listener = get_class($listener[0]) . ' -> ' . $listener[1];
                                }
                                else
                                {
                                    $listener = $listener[0] . ' :: ' . $listener[1];
                                }
                            }
                            else if ( is_string($listener) )
                            {
                                
                            }
                            else
                            {
                                $listener = 'ClosureObject';
                            }

                            $listenersList[] = $listener;
                        }
                    }

                    $paramsData = var_export($eventItem['event']->getParams(), true);
                    $eventsDataToAssign['call'][] = array('type' => $eventItem['type'], 'name' => $eventItem['event']->getName(), 'listeners' => $listenersList, 'params' => $paramsData, 'start' => sprintf('%.3f', $eventItem['start']), 'exec' => sprintf('%.3f', $eventItem['exec']));
                }

                $eventsDataToAssign['bindsCount'] = count($eventsDataToAssign['bind']);
                $eventsDataToAssign['callsCount'] = count($eventsDataToAssign['call']);
                $viewRenderer->assignVar('events', $eventsDataToAssign);
                //printVar($eventsDataToAssign);
                $output = $viewRenderer->renderTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'dev_tools_tpl.html');

                $viewRenderer->clearAssignedVars();
                $viewRenderer->assignVars($prevVars);


                $event->add($output);
            }
            PEEP::getEventManager()->bind('base.append_markup', 'base_dev_tool');
        }

        if ( !defined('PEEP_DEV_MODE') || !PEEP_DEV_MODE )
        {
            return;
        }
        else
        {
            $this->updateCachedEntities(PEEP_DEV_MODE);
        }

        if ( isset($_GET['clear']) && $_GET['clear'] = 'ctpl' )
        {
            PEEP_ViewRenderer::getInstance()->clearCompiledTpl();
        }

        if ( isset($_GET['set-theme']) )
        {
            $theme = BOL_ThemeService::getInstance()->findThemeByName(trim($_GET['theme']));

            if ( $theme !== null )
            {
                PEEP::getConfig()->saveConfig('base', 'selectedTheme', $theme->getName());
            }

            $this->redirect(PEEP::getRequest()->buildUrlQueryString(null, array('theme' => null)));
        }
    }

    protected function updateCachedEntities( $options )
    {
        $options = intval($options);

        if ( $options === 1 || $options & 1 << 1 )
        {
            PEEP_ViewRenderer::getInstance()->clearCompiledTpl();
        }

        if ( $options === 1 || $options & 1 << 2 )
        {
            BOL_ThemeService::getInstance()->updateThemeList();
            BOL_ThemeService::getInstance()->processAllThemes();

            if ( PEEP::getConfig()->configExists('base', 'cachedEntitiesPostfix') )
            {
                PEEP::getConfig()->saveConfig('base', 'cachedEntitiesPostfix', uniqid());
            }

            $event = new PEEP_Event('base.update_cache_entities');
            PEEP::getEventManager()->trigger($event);
        }

        if ( $options === 1 || $options & 1 << 3 )
        {
            BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
        }

        if ( $options === 1 || $options & 1 << 4 )
        {
            PEEP::getCacheManager()->clean(array(), PEEP_CacheManager::CLEAN_ALL);
        }

        if ( ( $options === 1 || $options & 1 << 5 ) && !defined('PEEP_PLUGIN_XP') )
        {
            $pluginService = BOL_PluginService::getInstance();
            $activePlugins = $pluginService->findActivePlugins();

            /* @var $pluginDto BOL_Plugin */
            foreach ( $activePlugins as $pluginDto )
            {
                $pluginStaticDir = PEEP_DIR_PLUGIN . $pluginDto->getModule() . DS . 'static' . DS;

                if ( file_exists($pluginStaticDir) )
                {
                    $staticDir = PEEP_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

                    if ( !file_exists($staticDir) )
                    {
                        mkdir($staticDir);
                        chmod($staticDir, 0777);
                    }

                    UTIL_File::copyDir($pluginStaticDir, $staticDir);
                }
            }
        }
    }

    protected function urlHostRedirect()
    {
        $urlArray = parse_url(PEEP_URL_HOME);
        $constHost = $urlArray['host'];

        if ( isset($_SERVER['HTTP_HOST']) && ( $_SERVER['HTTP_HOST'] !== $constHost ) )
        {
            $this->redirect(PEEP_URL_HOME . PEEP::getRequest()->getRequestUri());
        }
    }
    /**
     * @var array 
     */
    protected $httpsHandlerAttrsList = array();

    public function addHttpsHandlerAttrs( $controller, $action = false )
    {
        $this->httpsHandlerAttrsList[] = array(PEEP_RequestHandler::ATTRS_KEY_CTRL => $controller, PEEP_RequestHandler::ATTRS_KEY_ACTION => $action);
    }

    protected function httpVsHttpsRedirect()
    {
        $isSsl = PEEP::getRequest()->isSsl();

        if ( $isSsl === null )
        {
            return;
        }

        $attrs = PEEP::getRequestHandler()->getHandlerAttributes();
        $specAttrs = false;

        foreach ( $this->httpsHandlerAttrsList as $item )
        {
            if ( $item[PEEP_RequestHandler::ATTRS_KEY_CTRL] == $attrs[PEEP_RequestHandler::ATTRS_KEY_CTRL] && ( empty($item[PEEP_RequestHandler::ATTRS_KEY_ACTION]) || $item[PEEP_RequestHandler::ATTRS_KEY_ACTION] == $attrs[PEEP_RequestHandler::ATTRS_KEY_ACTION] ) )
            {
                $specAttrs = true;
                if ( !$isSsl )
                {
                    $this->redirect(str_replace("http://", "https://", PEEP_URL_HOME) . PEEP::getRequest()->getRequestUri());
                }
            }
        }

        if ( $specAttrs )
        {
            return;
        }

        $urlArray = parse_url(PEEP_URL_HOME);

        if ( !empty($urlArray["scheme"]) )
        {
            $homeUrlSsl = ($urlArray["scheme"] == "https");

            if ( ($isSsl && !$homeUrlSsl) || (!$isSsl && $homeUrlSsl) )
            {
                $this->redirect(PEEP_URL_HOME . PEEP::getRequest()->getRequestUri());
            }
        }
    }

    protected function handleHttps()
    {
        if ( !PEEP::getRequest()->isSsl() )
        {
            return;
        }

        function base_post_handle_https_static_content()
        {
            $markup = PEEP::getResponse()->getMarkup();
            $matches = array();
            preg_match_all("/<a([^>]+?)>(.+?)<\/a>/", $markup, $matches);
            $search = array_unique($matches[0]);
            $replace = array();
            $contentReplaceArr = array();

            for ( $i = 0; $i < sizeof($search); $i++ )
            {
                $replace[] = "<#|#|#" . $i . "#|#|#>";
                if ( mb_strstr($matches[2][$i], "http:") )
                {
                    $contentReplaceArr[] = $i;
                }
            }

            $markup = str_replace($search, $replace, $markup);
            $markup = str_replace("http:", "https:", $markup);

            foreach ( $contentReplaceArr as $index )
            {
                $search[$index] = str_replace($matches[2][$index], str_replace("http:", "https:", $matches[2][$index]), $search[$index]);
            }

            $markup = str_replace($replace, $search, $markup);

            PEEP::getResponse()->setMarkup($markup);
        }
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_DOCUMENT_RENDER, "base_post_handle_https_static_content");
    }

    protected function userAutoLogin()
    {
        if ( PEEP::getSession()->isKeySet('no_autologin') )
        {
            PEEP::getSession()->delete('no_autologin');
            return;
        }

        if ( !empty($_COOKIE['PEEP_login']) && !PEEP::getUser()->isAuthenticated() )
        {
            $id = BOL_UserService::getInstance()->findUserIdByCookie(trim($_COOKIE['peep_login']));

            if ( !empty($id) )
            {
                PEEP_User::getInstance()->login($id);
                $loginCookie = BOL_UserService::getInstance()->findLoginCookieByUserId($id);
                setcookie('peep_login', $loginCookie->getCookie(), (time() + 86400 * 7), '/', null, false, true);
            }
        }
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

    public function getContext()
    {
        return $this->context;
    }

    public function isMobile()
    {
        return $this->context == self::CONTEXT_MOBILE;
    }

    public function isDesktop()
    {
        return $this->context == self::CONTEXT_DESKTOP;
    }

    public function isApi()
    {
        return $this->context == self::CONTEXT_API;
    }
}
