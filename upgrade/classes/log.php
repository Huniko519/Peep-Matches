<?php

class UPDATE_Log
{
    const TYPE = 'type';
    const KEY = 'key';
    const TIME_STAMP = 'timeStamp';
    const MESSAGE = 'message';

    /**
     * Class instances.
     *
     * @var array
     */
    private static $classInstance;

    /**
     * Returns logger object.
     *
     * @param string $type
     * @return UPDATE_Log
     */
    public static function getInstance()
    {
        if( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    /**
     * Log type.
     *
     * @var string
     */
    private $type;
    /**
     * Log entries.
     *
     * @var array
     */
    private $entries = array();

    /**
     * Constructor.
     *
     * @param string $type
     * @param PEEP_LogWriter $logWriter
     */
    private function __construct()
    {
        $this->type = 'update';
    }

    /**
     * Adds log entry.
     *
     * @param string $message
     * @param string $key
     */
    public function addEntry( $message, $key = null )
    {
        $this->entries[] = array(self::TYPE => $this->type, self::KEY => $key, self::MESSAGE => $message, self::TIME_STAMP => time());        
    }

    /**
     * Returns all log entries.
     * 
     * @return array
     */
    public function getEntries()
    {
        return $this->entries;
    }
}