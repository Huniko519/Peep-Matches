<?php

final class PEEP_Response
{
    /**
     * HTTP Header constants
     */
    const HD_CACHE_CONTROL = 'Cache-Control';
    const HD_CNT_DISPOSITION = 'Content-Disposition';
    const HD_CNT_LENGTH = 'Content-Length';
    const HD_CONNECTION = 'Connection';
    const HD_PRAGMA = 'Pragma';
    const HD_CNT_TYPE = 'Content-Type';
    const HD_EXPIRES = 'Expires';
    const HD_LAST_MODIFIED = 'Last-Modified';
    const HD_LOCATION = 'Location';

    /**
     * Headers to send with response
     *
     * @var array
     */
    private $headers = array();

    /**
     * Document to send
     *
     * @var PEEP_Document
     */
    private $document;

    /**
     * Rendered markup
     *
     * @var string
     */
    private $markup = '';

    /**
     * Singleton instance.
     *
     * @var PEEP_Response
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Response
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
        
    }

    /**
     * @return PEEP_Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param PEEP_Document $document
     */
    public function setDocument( PEEP_Document $document )
    {
        $this->document = $document;
    }

    /**
     * Adds headers to response.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader( $name, $value )
    {
        $this->headers[trim($name)] = trim($value);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders( array $headers )
    {
        $this->headers = $headers;
    }

    /**
     * Clears all headers.
     */
    public function clearHeaders()
    {
        $this->headers = array();
    }

    /**
     * Sends all added headers.
     */
    public function sendHeaders()
    {
        if ( !headers_sent() )
        {
            foreach ( $this->headers as $headerName => $headerValue )
            {
                if ( substr(mb_strtolower($headerName), 0, 4) === 'http' )
                {
                    header($headerName . ' ' . $headerValue);
                }
                else if ( mb_strtolower($headerName) === 'status' )
                {
                    header(ucfirst(mb_strtolower($headerName)) . ': ' . $headerValue, null, (int) $headerValue);
                }
                else
                {
                    header($headerName . ':' . $headerValue);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param string $markup
     */
    public function setMarkup( $markup )
    {
        $this->markup = $markup;
    }

    /**
     * Sends generated response
     *
     */
    public function respond()
    {
        $event = new PEEP_Event(PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER);
        PEEP::getEventManager()->trigger($event);
        if ( $this->document !== null )
        {
            $renderedMarkup = $this->document->render();

            $event = new BASE_CLASS_EventCollector('base.append_markup');
            PEEP::getEventManager()->trigger($event);
            $data = $event->getData();
            $this->markup = str_replace(PEEP_Document::APPEND_PLACEHOLDER, PHP_EOL . implode(PHP_EOL, $data), $renderedMarkup);
        }

        $event = new PEEP_Event(PEEP_EventManager::ON_AFTER_DOCUMENT_RENDER);
        PEEP::getEventManager()->trigger($event);

        $this->sendHeaders();

        if ( PEEP::getRequest()->isAjax() )
        {
            exit();
        }

        if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
        {
            UTIL_Profiler::getInstance()->mark('final');
        }

        if ( PEEP_DEBUG_MODE )
        {
            echo ob_get_clean();
        }

        echo $this->markup;

        $event = new PEEP_Event('core.exit');
        PEEP::getEventManager()->trigger($event);
    }
}
