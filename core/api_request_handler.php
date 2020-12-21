<?php

final class PEEP_ApiRequestHandler extends PEEP_RequestHandler
{
    /**
     * Constructor.
     */
    private function __construct()
    {
        
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_ApiRequestHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_ApiRequestHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
//    protected function processCatchAllRequestsAttrs()
//    {
//        return null;
//    }
    
    /**
     * @param array $dispatchAttributes
     */
    public function dispatch()
    {
        // check if controller class contains package pointer with plugin key
        if ( empty($this->handlerAttributes[self::ATTRS_KEY_CTRL]) || !mb_strstr($this->handlerAttributes[self::ATTRS_KEY_CTRL], '_') )
        {
            throw new InvalidArgumentException("Can't dispatch request! Empty or invalid controller class provided!");
        }
        
        // set uri params in request object
        if ( !empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) )
        {
            PEEP::getRequest()->setUriParams($this->handlerAttributes[self::ATTRS_KEY_VARLIST]);
        }
        
        $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey($this->handlerAttributes[self::ATTRS_KEY_CTRL]));
        
        $catchAllRequests = $this->processCatchAllRequestsAttrs();
        
        if ( $catchAllRequests !== null )
        {
            $this->handlerAttributes = $catchAllRequests;
        }
        
        try
        {
            $reflectionClass = new ReflectionClass($this->handlerAttributes[self::ATTRS_KEY_CTRL]);
        }
        catch ( ReflectionException $e )
        {
            throw new Redirect404Exception();
        }
        
        /* @var $controller PEEP_ActionController */
        $controller = $reflectionClass->newInstance();

        // check if controller exists and is instance of base action controller class
        if ( $controller === null || !$controller instanceof PEEP_ApiActionController )
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

        if ( empty($this->handlerAttributes[self::ATTRS_KEY_ACTION]) )
        {
            $this->handlerAttributes[self::ATTRS_KEY_ACTION] = $controller->getDefaultAction();
        }

        try
        {
            $action = $reflectionClass->getMethod($this->handlerAttributes[self::ATTRS_KEY_ACTION]);
        }
        catch ( Exception $e )
        {
            throw new Redirect404Exception();
        }

        $args = array();
        
        $args[] = $_POST;
        $args[] = empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) ? array() : $this->handlerAttributes[self::ATTRS_KEY_VARLIST];
        
        $action->invokeArgs($controller, $args);

        PEEP::getDocument()->setBody($controller->render());
    }
}
