<?php

class PEEP_Dispatcher
{
    const ATTRS_KEY_CTRL = 'controller';
    const ATTRS_KEY_ACTION = 'action';
    const ATTRS_KEY_VARLIST = 'params';

    const CATCH_ALL_REQUEST_KEY_CTRL = 'controller';
    const CATCH_ALL_REQUEST_KEY_ACTION = 'action';
    const CATCH_ALL_REQUEST_KEY_REDIRECT = 'redirect';
    const CATCH_ALL_REQUEST_KEY_JS = 'js';
    const CATCH_ALL_REQUEST_KEY_ROUTE = 'route';
    const CATCH_ALL_REQUEST_KEY_PARAMS = 'params';

    /**
     * @var array
     */
    private $dispatchAttributes;
    /**
     * @var array
     */
    private $indexPageAttributes;
    /**
     * @var array
     */
    private $staticPageAttributes;
    /**
     * @var array
     */
    private $catchAllRequestsAttributes = array();
    /**
     * @var array
     */
    private $catchAllRequestsExcludes = array();

    /**
     * Constructor.
     */
    private function __construct()
    {
        
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Router
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Router
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
     * @return array
     */
    public function getCatchAllRequestsAttributes( $key )
    {
        return!empty($this->catchAllRequestsAttributes[$key]) ? $this->catchAllRequestsAttributes[$key] : null;
    }

    /**
     * <controller> <action> <params> <route> <redirect> <js>
     *
     * @param array $attributes 
     */
    public function setCatchAllRequestsAttributes( $key, array $attributes )
    {
        $this->catchAllRequestsAttributes[$key] = $attributes;

        $this->addCatchAllRequestsExclude($key, $attributes[self::ATTRS_KEY_CTRL], $attributes[self::ATTRS_KEY_ACTION]);
    }

    /**
     *
     * @param string $controller
     * @param string $action
     */
    public function addCatchAllRequestsExclude( $key, $controller, $action = null )
    {
        if ( empty($this->catchAllRequestsExcludes[$key]) )
        {
            $this->catchAllRequestsExcludes[$key] = array();
        }

        $this->catchAllRequestsExcludes[$key][] = array(self::CATCH_ALL_REQUEST_KEY_CTRL => $controller, self::CATCH_ALL_REQUEST_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getIndexPageAttributes()
    {
        return $this->indexPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setIndexPageAttributes( $controller, $action = 'index' )
    {
        $this->indexPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getStaticPageAttributes()
    {
        return $this->staticPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setStaticPageAttributes( $controller, $action = 'index' )
    {
        $this->staticPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getDispatchAttributes()
    {
        return $this->dispatchAttributes;
    }

    /**
     * @param array $attributes
     */
    public function setDispatchAttributes( array $attributes )
    {
        if ( empty($attributes[PEEP_Route::DISPATCH_ATTRS_CTRL]) )
        {
            throw new Redirect404Exception();
        }

        $this->dispatchAttributes = array(
            self::ATTRS_KEY_CTRL => trim($attributes[PEEP_Route::DISPATCH_ATTRS_CTRL]),
            self::ATTRS_KEY_ACTION => ( empty($attributes[PEEP_Route::DISPATCH_ATTRS_ACTION]) ? null : trim($attributes[PEEP_Route::DISPATCH_ATTRS_ACTION]) ),
            self::ATTRS_KEY_VARLIST => ( empty($attributes[PEEP_Route::DISPATCH_ATTRS_VARLIST]) ? array() : $attributes[PEEP_Route::DISPATCH_ATTRS_VARLIST])
        );
    }

    /**
     * @param array $dispatchAttributes
     */
    public function dispatch()
    {
        // check if controller class contains package pointer with plugin key
        if ( empty($this->dispatchAttributes[self::ATTRS_KEY_CTRL]) || !mb_strstr($this->dispatchAttributes[self::ATTRS_KEY_CTRL], '_') )
        {
            throw new InvalidArgumentException("Can't dispatch request! Empty or invalid controller class provided!");
        }

        // set uri params in request object
        if ( !empty($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]) )
        {
            PEEP::getRequest()->setUriParams($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]);
        }

        $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey($this->dispatchAttributes[self::ATTRS_KEY_CTRL]));

        $catchAllRequests = $this->processCatchAllRequestsAttrs();

        if ( $catchAllRequests !== null )
        {
            $this->dispatchAttributes = $catchAllRequests;
        }

        /* @var $controller PEEP_ActionController */

        try
        {
            $controller = PEEP::getClassInstance($this->dispatchAttributes[self::ATTRS_KEY_CTRL]);
        }
        catch ( ReflectionException $e )
        {
            throw new Redirect404Exception();
        }
        
        // check if controller exists and is instance of base action controller class
        if ( $controller === null || !$controller instanceof PEEP_ActionController )
        {
            throw new LogicException("Can't dispatch request! Please provide valid controller class!");
        }

        // redirect to page 404 if plugin is inactive and isn't instance of admin controller class
        if ( !$plugin->isActive() && !$controller instanceof ADMIN_CTRL_Abstract )
        {
            throw new Redirect404Exception();
        }

        // call optional init method
        $controller->init();

        if ( empty($this->dispatchAttributes[self::ATTRS_KEY_ACTION]) )
        {
            $this->dispatchAttributes[self::ATTRS_KEY_ACTION] = $controller->getDefaultAction();
        }

        try
        {
            $action = $reflectionClass->getMethod($this->dispatchAttributes[self::ATTRS_KEY_ACTION]);
        }
        catch ( Exception $e )
        {
            throw new Redirect404Exception();
        }

        $action->invokeArgs($controller, array(self::ATTRS_KEY_VARLIST => ( empty($this->dispatchAttributes[self::ATTRS_KEY_VARLIST]) ? array() : $this->dispatchAttributes[self::ATTRS_KEY_VARLIST] )));

        // set default template for controller action if template wasn't set
        if ( $controller->getTemplate() === null )
        {
            $controller->setTemplate($this->getControllerActionDefaultTemplate());
        }

        PEEP::getDocument()->setBody($controller->render());
    }

    /**
     * Returns template path for provided controller and action.
     *
     * @param string $controller
     * @param string $action
     * @return string<path>
     */
    private function getControllerActionDefaultTemplate()
    {
        $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey($this->dispatchAttributes[self::ATTRS_KEY_CTRL]));

        $templateFilename = PEEP::getAutoloader()->classToFilename($this->dispatchAttributes[self::ATTRS_KEY_CTRL], false) . '_'
            . PEEP::getAutoloader()->classToFilename(ucfirst($this->dispatchAttributes[self::ATTRS_KEY_ACTION]), false) . '.html';

        return $plugin->getCtrlViewDir() . $templateFilename;
    }

    /**
     * Returns processed catch all requests attributes.
     *
     * @return string
     */
    private function processCatchAllRequestsAttrs()
    {
        if ( empty($this->catchAllRequestsAttributes) )
        {
            return null;
        }

        $catchRequest = true;

        $lastKey = array_search(end($this->catchAllRequestsAttributes), $this->catchAllRequestsAttributes);

        foreach ( $this->catchAllRequestsExcludes[$lastKey] as $exclude )
        {
            if ( $this->dispatchAttributes[self::ATTRS_KEY_CTRL] === $exclude[self::CATCH_ALL_REQUEST_KEY_CTRL] && ( $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] === null || $this->dispatchAttributes[self::ATTRS_KEY_ACTION] === $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] ) )
            {
                $catchRequest = false;
                break;
            }
        }
        if ( $catchRequest )
        {
            if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT] )
            {
                $route = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) ? trim($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) : null;

                $params = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS]) ? $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS] : array();

                $redirectUrl = ($route !== null) ?
                    PEEP::getRouter()->urlForRoute($route, $params) :
                    PEEP::getRouter()->urlFor($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_CTRL], $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ACTION], $params);

                $redirectUrl = PEEP::getRequest()->buildUrlQueryString($redirectUrl, array('back_uri' => PEEP::getRequest()->getRequestUri()));

                if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS] )
                {
                    // TODO resolve hotfix
                    // hotfix for splash screen + members only case
                    if ( array_key_exists('base.members_only', $this->catchAllRequestsAttributes) )
                    {
                        if ( $this->dispatchAttributes[self::CATCH_ALL_REQUEST_KEY_CTRL] === 'BASE_CTRL_User' && $this->dispatchAttributes[self::CATCH_ALL_REQUEST_KEY_ACTION] === 'standardSignIn' )
                        {
                            $backUri = isset($_GET['back_uri']) ? $_GET['back_uri'] : PEEP::getRequest()->getRequestUri();
                            PEEP::getDocument()->addOnloadScript("window.location = '" . PEEP::getRequest()->buildUrlQueryString($redirectUrl, array('back_uri' => $backUri)) . "'");
                            return null;
                        }
                        else
                        {
                            $ru = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('static_sign_in'), array('back_uri' => PEEP::getRequest()->getRequestUri()));
                            PEEP::getApplication()->redirect($ru);
                        }
                    }

                    PEEP::getDocument()->addOnloadScript("window.location = '" . $redirectUrl . "'");
                    return null;
                }

                UTIL_Url::redirect($redirectUrl);
            }

            return $this->getCatchAllRequestsAttributes($lastKey);
        }

        return null;
    }
}
