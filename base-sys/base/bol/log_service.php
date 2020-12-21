<?php

final class BOL_LogService
{
    /**
     * @var BOL_LogDao
     */
    private $logDao;
    /**
     * Singleton instance.
     *
     * @var BOL_CommentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentDao
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
        $this->logDao = BOL_LogDao::getInstance();
    }

    /**
     * Adds entries.
     *
     * @param array $entries
     */
    public function addEntries( array $entries )
    {
        $objectList = array();

        if ( !empty($entries) )
        {
            foreach ( $entries as $entry )
            {
                $obj = new BOL_Log();
                $obj->setKey($entry[PEEP_Log::KEY]);
                $obj->setType($entry[PEEP_Log::TYPE]);
                $obj->setMessage($entry[PEEP_Log::MESSAGE]);
                $obj->setTimeStamp($entry[PEEP_Log::TIME_STAMP]);

                $objectList[] = $obj;
            }
        }

        $this->logDao->addEntries($objectList);
    }

    /**
     * Returns log list for provided type.
     *
     * @param string $type
     * @return array<BOL_Log>
     */
    public function findByType( $type )
    {
        return $this->logDao->findByType($type);
    }

    /**
     * Returns log item for provided type and key.
     *
     * @param string $type
     * @param string $key
     * @return BOL_Log
     */
    public function findByTypeAndKey( $type, $key )
    {
        return $this->logDao->findByTypeAndKey($type, $key);
    }
}