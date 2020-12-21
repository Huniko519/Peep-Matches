<?php

/**
 * Redirect exception forces 301 http redirect.
 */
class RedirectException extends Exception
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var integer
     */
    private $redirectCode;
    /**
     * @var mixed
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct( $url, $code = null )
    {
        parent::__construct('', 0);
        $this->url = $url;
        $this->redirectCode = ( empty($code) ? 301 : (int) $code );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return integer
     */
    public function getRedirectCode()
    {
        return $this->redirectCode;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }
}

class InterceptException extends Exception
{
    private $handlerAttrs;

    public function __construct( $attrs )
    {
        $this->handlerAttrs = $attrs;
    }

    public function getHandlerAttrs()
    {
        return $this->handlerAttrs;
    }
}

class AuthorizationException extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = PEEP::getRouter()->getRoute('base_page_auth_failed');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'authorizationFailed') : $route->getDispatchAttrs();
        $params[PEEP_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}



/**
 * Page not found 404 redirect exception.
 */
class Redirect404Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $route = PEEP::getRouter()->getRoute('base_page_404');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page404') : $route->getDispatchAttrs();
        parent::__construct($params);
    }
}

/**
 * Internal server error redirect exception.
 */
class Redirect500Exception extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PEEP_URL_HOME . '500.phtml', 500);
    }
}

/**
 * Forbidden 403 redirect exception.
 */
class Redirect403Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = PEEP::getRouter()->getRoute('base_page_403');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page403') : $route->getDispatchAttrs();
        $params[PEEP_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}

/**
 * Blank confirm page redirect exception.
 */
class RedirectConfirmPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_page_confirm'), array('back_uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
        PEEP::getSession()->set('baseConfirmPageMessage', $message);
    }
}

/**
 * Blank message page redirect exception.
 */
class RedirectAlertPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(PEEP::getRouter()->urlForRoute('base_page_alert'));
        PEEP::getSession()->set('baseAlertPageMessage', $message);
    }
}

/**
 * Sign in page redirect exception.
 */
class AuthenticateException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
    }
}

class ApiResponseErrorException extends Exception
{
    public $data = array();
    
    public function __construct($data = array(), $code = 0) 
    {
        parent::__construct("", $code);
        
        $this->data = $data;
    }
}

class ApiAccessException extends ApiResponseErrorException
{
    const TYPE_NOT_AUTHENTICATED = "not_authenticated";
    const TYPE_SUSPENDED = "suspended";
    const TYPE_NOT_APPROVED = "not_approved";
    const TYPE_NOT_VERIFIED = "not_verified";
    
    public $data = array();
    
    public function __construct( $type ) 
    {
        parent::__construct(array(
            "type" => $type
        ));
    }
}