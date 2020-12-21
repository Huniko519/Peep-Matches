<?php

final class PEEP_Request
{
    /**
     * Request uri.
     *
     * @var string
     */
    private $uri;
    private $uriParams;

    /**
     * Singleton instance.
     *
     * @var PEEP_Request
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Request
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
     * Constructor.
     */
    private function __construct()
    {
        if ( get_magic_quotes_gpc() )
        {
            $_GET = $this->stripSlashesRecursive($_GET);
            $_POST = $this->stripSlashesRecursive($_POST);
        }
    }

    /**
     * @return array
     */
    public function getUriParams()
    {
        return $this->uriParams;
    }

    /**
     * @param array $uriParams
     */
    public function setUriParams( array $uriParams )
    {
        $this->uriParams = $uriParams;
    }

    /**
     * Returns real request uri.
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ( $this->uri === null )
        {
            $this->uri = UTIL_Url::getRealRequestUri(PEEP::getRouter()->getBaseUrl(), $_SERVER['REQUEST_URI']);
        }

        return $this->uri;
    }

    /**
     * Returns remote ip address.
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Returns request type.
     *
     * @return string
     */
    public function getRequestType()
    {
        return mb_strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }
//    public function getContentType()
//    {
//        return $_SERVER[''];
//    }

    /**
     * Indicates if request is ajax.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && mb_strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' );
    }

    /**
     * Indicates if request is post.
     *
     * @return boolean
     */
    public function isPost()
    {
        return ( mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' );
    }

    /**
     * Returns request agent name.
     *
     * @return string
     */
    public function getUserAgentName()
    {
        return UTIL_Browser::getBrowser($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Returns user agent version;
     *
     * @return string
     */
    public function getUserAgentVersion()
    {
        return UTIL_Browser::getVersion($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Returns request agent platform.
     *
     * @return string
     */
    public function getUserAgentPlatform()
    {
        return UTIL_Browser::getPlatform($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Indicates if user agent is mobile.
     *
     * @return boolean
     */
    public function isMobileUserAgent()
    {
        return UTIL_Browser::isMobile($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Builds and updates url query string.
     *
     * @param string $url
     * @param array $paramsToUpdate
     * @param string $anchor
     * @return string
     */
    public function buildUrlQueryString( $url = null, array $paramsToUpdate = array(), $anchor = null )
    {
        $url = ( $url === null ) ? PEEP_URL_HOME . $this->getRequestUri() : trim($url);

        $requestUrlArray = parse_url($url);

        $currentParams = array();

        if ( isset($requestUrlArray['query']) )
        {
            parse_str($requestUrlArray['query'], $currentParams);
        }

        $currentParams = array_merge($currentParams, $paramsToUpdate);

        return (empty($requestUrlArray['scheme']) ? "" : $requestUrlArray['scheme'] ) . '://' . $requestUrlArray['host'] . ( empty($requestUrlArray['path']) ? '' : $requestUrlArray['path'] ) .
            ( empty($requestUrlArray['port']) ? '' : ':' . (int) $requestUrlArray['port'] ) . '?' . http_build_query($currentParams) . ( $anchor === null ? '' : '#' . trim($anchor) );
    }

    /**
     * @param array $value
     * @return array
     */
    private function stripSlashesRecursive( $value )
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesRecursive'), $value) : stripslashes($value);
        return $value;
    }

    public function isSsl()
    {
        $isHttps = null;

        if ( array_key_exists("HTTPS", $_SERVER) )
        {
            $isHttps = ($_SERVER["HTTPS"] == "on");
        }
        else if ( array_key_exists("REQUEST_SCHEME", $_SERVER) )
        {
            $isHttps = (strtolower($_SERVER["REQUEST_SCHEME"]) == "https");
        }
        else if ( array_key_exists("SERVER_PORT", $_SERVER) )
        {
            $isHttps = ($_SERVER["SERVER_PORT"] == "443");
        }

        return $isHttps;
    }
}
