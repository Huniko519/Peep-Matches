<?php

class PEEP_Route
{
    const PARAM_OPTION_DEFAULT_VALUE = 'default';
    const PARAM_OPTION_HIDDEN_VAR = 'var';
    const PARAM_OPTION_VALUE_REGEXP = 'regexp';
    const DISPATCH_ATTRS_CTRL = 'controller';
    const DISPATCH_ATTRS_ACTION = 'action';
    const DISPATCH_ATTRS_VARLIST = 'vars';

    /**
     * Route name.
     *
     * @var string
     */
    private $routeName;
    /**
     * Route URI pattern with vars (simple string for static routes). 
     *
     * @var string
     */
    private $routePath;
    /**
     * Decomposed URI parts.
     *
     * @var array
     */
    private $routePathArray;
    /**
     * Flag indicating if route path is static.
     * 
     * @var boolean
     */
    private $isStatic = false;
    /**
     * Result attributes for dispatching process.
     *
     * @var array
     */
    private $dispatchAttrs = array();
    /**
     * Default route params.
     * 
     * @var array
     */
    private $routeParamOptions = array();

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getDispatchAttrs()
    {
        return $this->dispatchAttrs;
    }

    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->routePath;
    }

    /**
     * @param string $routeName
     */
    public function setRouteName( $routeName )
    {
        if ( $routeName )
        {
            $this->routeName = trim($routeName);
        }
    }

    /**
     * @param string $routePath
     */
    public function setRoutePath( $routePath )
    {
        if ( $routePath )
        {
            $this->routePath = UTIL_String::removeFirstAndLastSlashes(trim($routePath));
            $this->routePathArray = explode('/', $this->routePath);
        }
    }

    /**
     * @param array $dispatchAttrs
     */
    public function setDispatchAttrs( array $dispatchAttrs )
    {
        if ( !empty($dispatchAttrs['controller']) && !empty($dispatchAttrs['action']) )
        {
            $this->dispatchAttrs = $dispatchAttrs;
        }
    }

    /**
     * @param array $routeParamOptions
     */
    public function setRouteParamOptions( array $routeParamOptions )
    {
        $this->routeParamOptions = $routeParamOptions;
    }

    /**
     * @return boolean
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /**
     * Constructor.
     *
     * @throws InvalidArgumentException
     * @param string $routeName
     * @param string $routePath
     * @param string $controller
     * @param string $action
     * @param array $paramOptions
     */
    public function __construct( $routeName, $routePath, $controller, $action, array $paramOptions = array() )
    {
        if ( empty($routeName) || empty($routePath) || empty($controller) || empty($action) )
        {
            throw new InvalidArgumentException('Invalid route params provided!');
        }
        
        $this->routeParamOptions = $paramOptions;
        $this->routeName = trim($routeName);
        $this->setRoutePath($routePath);
        $this->dispatchAttrs[self::DISPATCH_ATTRS_CTRL] = trim($controller);
        $this->dispatchAttrs[self::DISPATCH_ATTRS_ACTION] = trim($action);

        // if there are no dynamic parts in route path -> set flag and return
        if ( !mb_strstr($this->routePath, ':') )
        {
            $this->isStatic = true;
            return;
        }
    }

    /**
     * Adds options to route params.
     *
     * @param string $paramName
     * @param string $option
     * @param mixed $optionValue
     */
    public function addParamOption( $paramName, $option, $optionValue )
    {
        if ( empty($this->routeParamOptions[$paramName]) )
        {
            $this->routeParamOptions[$paramName] = array();
        }

        $this->routeParamOptions[$paramName][$option] = $optionValue;
    }

    /**
     * Generates route path uri for provided set of params.
     *
     * @throws InvalidArgumentException
     * @param array $params
     * @return string
     */
    public function generateUri( $params = array() )
    {
        // if route path is static we can return it without params processing 
        if ( $this->isStatic )
        {
            return $this->routePath;
        }

        $generatedUri = '';

        foreach ( $this->routePathArray as $value )
        {
            if ( mb_substr($value, 0, 1) !== ':' )
            {
                $generatedUri .= $value . '/';
            }
            else
            {
                $varName = mb_substr($value, 1);

                if ( !isset($params[$varName]) && !isset($this->routeParamOptions[$varName][self::PARAM_OPTION_DEFAULT_VALUE]) )
                {
                    trigger_error('Empty var for route provided. VarName - `' . $varName . '`!', E_USER_WARNING);
                    return 'INVALID_URI';
                }

                if ( isset($this->routeParamOptions[$varName][self::PARAM_OPTION_VALUE_REGEXP]) && !preg_match($this->routeParamOptions[$varName][self::PARAM_OPTION_VALUE_REGEXP], $params[$varName]) )
                {
                    trigger_error('Invalid var for route provided. VarName - `' . $varName . '`!', E_USER_WARNING);
                    return 'INVALID_URI';
                }

                $generatedUri .= urlencode(( isset($params[$varName]) ? $params[$varName] : $this->routeParamOptions[$varName][self::PARAM_OPTION_DEFAULT_VALUE])) . '/';
            }
        }

        return mb_substr($generatedUri, 0, -1);
    }

    /**
     * Tries to match route path and provided URI.
     *
     * @param string $uri
     * @return boolean
     */
    public function match( $uri )
    {
        $uri = UTIL_String::removeFirstAndLastSlashes(trim($uri));

        $this->dispatchAttrs[self::DISPATCH_ATTRS_VARLIST] = array();

        foreach ( $this->routeParamOptions as $paramName => $paramArray )
        {
            if ( isset($paramArray[self::PARAM_OPTION_HIDDEN_VAR]) )
            {
                $this->dispatchAttrs[self::DISPATCH_ATTRS_VARLIST][$paramName] = $paramArray[self::PARAM_OPTION_HIDDEN_VAR];
            }
        }

        if ( $this->isStatic )
        {
            $pathArray = explode('/', $this->routePath);
            $pathArray = array_map('urlencode', $pathArray);
            $tempPath = implode('/', $pathArray);

            return ( mb_strtoupper($uri) === mb_strtoupper($tempPath));
        }

        $uriArray = explode('/', $uri);

        if ( sizeof($uriArray) !== sizeof($this->routePathArray) )
        {
            return false;
        }

        foreach ( $this->routePathArray as $key => $value )
        {
            if ( !mb_strstr($value, ':') )
            {
                if ( mb_strtoupper(urlencode($value)) !== mb_strtoupper($uriArray[$key]) )
                {
                    return false;
                }
            }
            else
            {
                if ( isset($this->routeParamOptions[mb_substr($value, 1)][self::PARAM_OPTION_VALUE_REGEXP]) && !preg_match($this->routeParamOptions[mb_substr($value, 1)][self::PARAM_OPTION_VALUE_REGEXP], $uriArray[$key]) )
                {
                    return false;
                }

                $this->dispatchAttrs[self::DISPATCH_ATTRS_VARLIST][mb_substr($value, 1)] = $uriArray[$key];
            }
        }

        return true;
    }
}
