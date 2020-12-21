<?php

final class PEEP_ErrorManager
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
     * @var BASE_CLASS_ErrOutput
     */
    private $errorOutput;

    /**
     * @var PEEP_Log 
     */
    private $logger;

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
     * @return PEEP_Log
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger( PEEP_Log $logger )
    {
        $this->logger = $logger;
    }

    /**
     * @return BASE_CLASS_ErrOutput
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * @param BASE_CLASS_ErrOutput $errorOutput
     */
    public function setErrorOutput( BASE_CLASS_ErrOutput $errorOutput )
    {
        $this->errorOutput = $errorOutput;
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

        //temp fix
        $e_depricated = defined('E_DEPRECATED') ? E_DEPRECATED : 0;

        switch ( $errno )
        {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case $e_depricated:

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
            'trace' => $e->getTraceAsString(),
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
        $this->errorOutput->printString($data);
        $this->handleLog($data);
    }

    private function handleDie( $data )
    {
        $this->errorOutput->printString($data);
        $this->handleLog($data);

        PEEP::getEventManager()->trigger(new PEEP_Event('core.emergency_exit', $data));
        exit;
    }

    private function handleRedirect( $data )
    {
        $this->handleLog($data);
        PEEP::getEventManager()->trigger(new PEEP_Event('core.emergency_exit', $data));

        header("HTTP/1.1 500 Internal Server Error");
        header('Location: ' . PEEP_URL_HOME . 'e500.php');
    }

    private function handleIgnore( $data )
    {
        $this->handleLog($data);
        return;
    }

    private function handleLog( $data )
    {
        if ( $this->logger === null )
        {
            return;
        }

        $trace = !empty($data['trace']) ? ' Trace: [' . str_replace(PHP_EOL, ' | ', $data['trace']) . ']' : "";
        $message = 'Message: ' . $data['message'] . ' File: ' . $data['file'] . ' Line:' . $data['line'] . $trace;
        $this->logger->addEntry($message, $data['type']);
    }

    public function debugBacktrace( )
    {
        $stack = '';
        $i = 1;
        $trace = debug_backtrace();
        unset($trace[0]);

        foreach ( $trace as $node )
        {
            $stack .=  "#$i " . (isset($node['file']) ? $node['file'] : '') . (isset($node['line']) ? "(" . $node['line'] . "): " : '');
            if ( isset($node['class']) )
            {
                $stack .= $node['class'] . "->";
            }
            $stack .= $node['function'] . "()" . PHP_EOL;
            $i++;
        }

        return $stack;
    }
}
