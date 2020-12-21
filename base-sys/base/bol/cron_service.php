<?php

class BOL_CronService
{
    private $cronJobDao;

    private function __construct()
    {
        $this->cronJobDao = BOL_CronJobDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_CronService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CronService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function findJobList()
    {
        return $this->cronJobDao->findAll();
    }

    public function batchSave( array $objects )
    {
        if ( is_array($objects) && count($objects) > 0 )
        {
            $this->cronJobDao->batchSave($objects);
        }
    }
}
