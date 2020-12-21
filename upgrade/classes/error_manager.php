<?php

final class UPDATE_ErrorManager
{
    /**
     * Singleton instance.
     *
     * @var PEEP_ErrorManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_ErrorManager
     */
    public static function getInstance( $debugMode = true )
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self($debugMode);
        }

        return self::$classInstance;
    }
    /**
     * @var boolean
     */
    private $debugMode;
    /**
     * @var string
     */
    private $errorPageUrl;

    /**
     * Constructor.
     */
    private function __construct( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;

        // set custom error and exception interceptors
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));

        // set error reporting level
        error_reporting(-1);
    }

    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    /**
     * @param boolean $debugMode
     */
    public function setDebugMode( $debugMode )
    {
        $this->debugMode = (bool) $debugMode;
    }

    /**
     * @return string
     */
    public function getErrorPageUrl()
    {
        return $this->errorPageUrl;
    }

    /**
     * @param string $errorPageUrl
     */
    public function setErrorPageUrl( $errorPageUrl )
    {
        $this->errorPageUrl = $errorPageUrl;
    }

    /**
     * Custom error handler.
     *
     * @param integer $errno
     * @param string $errString
     * @param string $errFile
     * @param integer $errLine
     * @return boolean
     */
    public function errorHandler( $errno, $errString, $errFile, $errLine )
    {
        // ignore if line is prefixed by `@`
        if ( error_reporting() === 0 )
        {
            return true;
        }

        $data = array(
            'message' => $errString,
            'file' => $errFile,
            'line' => $errLine
        );

        switch ( $errno )
        {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT;
                $data['type'] = 'Notice';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_CORE_WARNING:
                $data['type'] = 'Warning';

                if ( $this->debugMode )
                {
                    $this->handleShow($data);
                }
                else
                {
                    $this->handleIgnore($data);
                }
                break;

            default:
                $data['type'] = 'Error';

                if ( $this->debugMode )
                {
                    $this->handleDie($data);
                }
                else
                {
                    $this->handleRedirect($data);
                }
                break;
        }

        return true;
    }

    /**
     * Custom exception handler.
     *
     * @param Exception $e
     */
    public function exceptionHandler( Exception $e )
    {
        $data = array(
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => '<pre>' . $e->getTraceAsString() . '</pre>',
            'type' => 'Exception',
            'class' => get_class($e)
        );

        if ( $this->debugMode )
        {
            $this->handleDie($data);
        }
        else
        {
            $this->handleRedirect($data);
        }
    }

    private function handleShow( $data )
    {
        UTIL_Debug::printDebugMessage($data);
    }

    private function handleDie( $data )
    {
        UTIL_Debug::printDebugMessage($data);
        exit;
    }

    private function handleRedirect( $data )
    {
//        header("HTTP/1.1 500 Internal Server Error");
//        header('Location: ' . PEEP_URL_HOME . '500.phtml');
    }

    private function handleIgnore( $data )
    {
        return;
    }
}
