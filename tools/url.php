<?php

class UTIL_Url
{
    private static $redirectCodes = array(
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );

    /**
     * Makes search engines friendly redirect to provided URL.
     * 
     * @param string $url
     * @param integer $redirectCode
     */
    public static function redirect( $url, $redirectCode = null )
    {
        $redirectCode = array_key_exists((int) $redirectCode, self::$redirectCodes) ? (int) $redirectCode : 301;

        header(self::$redirectCodes[$redirectCode]);
        header("Location: " . $url);
        exit();
    }

    /**
     * Removes site installation subfolder from request URI
     * 
     * @param string $urlHome
     * @param string $requestUri
     * @return string
     */
    public static function getRealRequestUri( $urlHome, $requestUri )
    {
        $urlArray = parse_url($urlHome);

        $originalUri = UTIL_String::removeFirstAndLastSlashes($requestUri);
        $originalPath = UTIL_String::removeFirstAndLastSlashes($urlArray['path']);

        if ( $originalPath === '' )
        {
            return $originalUri;
        }

        $uri = mb_substr($originalUri, (mb_strpos($originalUri, $originalPath) + mb_strlen($originalPath)));
        $uri = trim(UTIL_String::removeFirstAndLastSlashes($uri));

        return $uri ? self::secureUri($uri) : '';
    }

   /**
    * Secure uri
    *
    * @param string $uri
    * @return string
    */
    public static function secureUri( $uri )
    {
        // remove posible native uri encoding
        $uriInfo = parse_url(urldecode($uri));

        if ( $uriInfo )
        {
            $processedUri = '';

            // process uri path
            if ( !empty($uriInfo['path']) ) 
            {
                $processedUri = implode('/', array_map('urlencode', explode('/', $uriInfo['path'])));
            }

            // process uri params
            if ( !empty($uriInfo['query']) )
            {
                // parse uri params
                $uriParams = array();
                parse_str($uriInfo['query'], $uriParams);

                $processedUri .= '?' . http_build_query($uriParams);
            }

            if ( !empty($uriInfo['fragment']) )
            {
                $processedUri .= '#' . urlencode($uriInfo['fragment']);
            }

            return $processedUri;
        }
    }

    public static function selfUrl()
    {
        $s = (!empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on") ) ? 's' : '';
        $serverProtocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($serverProtocol, 0, strpos($serverProtocol, '/')) . $s;

        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);

        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . self::secureUri($_SERVER['REQUEST_URI']);
    }

    public static function getLocalPath( $uri )
    {
        $userFilesUrl = PEEP::getStorage()->getFileUrl(PEEP_DIR_USERFILES);
        $path = null;

        if ( stripos($uri, PEEP_URL_HOME) !== false )
        {
            $path = str_replace(PEEP_URL_HOME, PEEP_DIR_ROOT, $uri);
            $path = str_replace('/', DS, $path);
        }
        else if ( stripos($uri, $userFilesUrl) !== false )
        {
            $path = str_replace($userFilesUrl, PEEP_DIR_USERFILES, $uri);
            $path = str_replace('/', DS, $path);
        }

        return $path;
    }
}
