<?php

class PEEP_Router
{
    /**
     * Current request uri.
     *
     * @var string
     */
    private $uri;
    /**
     * Static routes.
     *
     * @var array
     */
    private $staticRoutes = array();
    /**
     * Dynamic routes.
     *
     * @var array
     */
    private $routes = array();
    /**
     * Default route. Used for default url generation strategy.
     * 
     * @var DefaultRoute
     */
    private $defaultRoute;
    /**
     * Base url is added to all generated URIs.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * @var PEEP_Route
     */
    private $usedRoute;

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
     * @return DefaultRoute
     */
    public function getDefaultRoute()
    {
        return $this->defaultRoute;
    }

    /**
     * @param DefaultRoute $defaultRoute
     * @return PEEP_Router
     */
    public function setDefaultRoute( PEEP_DefaultRoute $defaultRoute )
    {
        $this->defaultRoute = $defaultRoute;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return PEEP_Router
     */
    public function setUri( $uri )
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return PEEP_Router
     */
    public function setBaseUrl( $baseUrl )
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return array('staticRoutes' => $this->staticRoutes, 'routes' => $this->routes);
    }

    /**
     * Adds route object to router.
     * All routes should by added before routing process starts.
     * If route with provided name exists exception will be thrown.
     *
     * @throws LogicException
     * @param PEEP_RouteAbstract $route
     * @return PEEP_Router
     */
    public function addRoute( PEEP_Route $route )
    {
        $routeName = $route->getRouteName();

        if ( isset($this->staticRoutes[$routeName]) || isset($this->routes[$routeName]) )
        {
            //throw new LogicException( "Can't add route! Route `" . $routeName . "` already exists!");
            trigger_error("Can't add route! Route `" . $routeName . "` already added!", E_USER_WARNING);
        }
        else
        {
            if ( $route->isStatic() )
            {
                $this->staticRoutes[$routeName] = $route;
            }
            else
            {
                $this->routes[$routeName] = $route;
            }
        }

        return $this;
    }

    /**
     * Removes route object from router.
     * Routes should be removed before routing process starts.
     *
     * @param string $routeName
     * @return PEEP_Router
     */
    public function removeRoute( $routeName )
    {
        $routeName = trim($routeName);

        if ( isset($this->staticRoutes[$routeName]) )
        {
            unset($this->staticRoutes[$routeName]);
        }

        if ( isset($this->routes[$routeName]) )
        {
            unset($this->routes[$routeName]);
        }

        return $this;
    }

    /**
     * Returns route with provided name.
     *
     * @param string $routeName
     * @return PEEP_Route
     */
    public function getRoute( $routeName )
    {
        $routeName = trim($routeName);

        if ( isset($this->staticRoutes[$routeName]) )
        {
            return $this->staticRoutes[$routeName];
        }

        if ( isset($this->routes[$routeName]) )
        {
            return $this->routes[$routeName];
        }

        return null;
    }

    /**
     * Generates uri for route using provided params.
     *
     * @param string $routeName
     * @param array $params
     * @return string
     */
    public function uriForRoute( $routeName, array $params = array() )
    {
        $routeName = trim($routeName);

        if ( isset($this->staticRoutes[$routeName]) )
        {
            return $this->staticRoutes[$routeName]->generateUri($params);
        }

        if ( isset($this->routes[$routeName]) )
        {
            return $this->routes[$routeName]->generateUri($params);
        }

        trigger_error("Can't generate URI! Route `" . $routeName . "` not found!", E_USER_WARNING);

        return 'INVALID_URI';
    }

    /**
     * Generates url for route using provided params.
     *
     * @param string $routeName
     * @param array $params
     * @return string
     */
    public function urlForRoute( $routeName, array $params = array() )
    {
        return $this->baseUrl . $this->uriForRoute($routeName, $params);
    }

    /**
     * Generates url by default route for provided params.
     *
     * @throws InvalidArgumentException
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function urlFor( $controller, $action = null, array $params = array() )
    {
        //return $this->baseUrl . $this->uriFor($controller, $action, $params);

        //temp fix for mobile version
        return (mb_stristr($controller, '_mctrl_') ? $this->baseUrl : $this->getBaseUrl()) . $this->uriFor($controller, $action, $params);
    }

    /**
     * Generates uri by default route for provided params.
     *
     * @throws InvalidArgumentException
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function uriFor( $controller, $action = null, array $params = array() )
    {
        return $this->defaultRoute->generateUri($controller, $action, $params);
    }

    /**
     * Returns routing result - array with params (module, controller, action).
     * Tries to match requested URI with all added routes. 
     * If matches weren't found default route is used.
     *
     * @throws Redirect404Exception
     * @return array
     */
    public function route()
    {
        foreach ( $this->staticRoutes as $route )
        {
            if ( $route->match($this->uri) )
            {
                $this->usedRoute = $route;
                return $route->getDispatchAttrs();
            }
        }
        
        foreach ( $this->routes as $route )
        {
            if ( $route->match($this->uri) )
            {
                $this->usedRoute = $route;
                return $route->getDispatchAttrs();
            }
        }

        return $this->defaultRoute->getDispatchAttrs($this->uri);
    }
    
    /**
     * Returns all added routes.
     * 
     * @return array
     */
    public function getAddedRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Returns used route.
     * 
     * @return type 
     */
    public function getUsedRoute()
    {
        return $this->usedRoute;
    }
}