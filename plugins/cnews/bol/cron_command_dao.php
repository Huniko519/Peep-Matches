<?php

class CNEWS_BOL_CronCommandDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_CronCommandDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_CronCommandDao
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
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'CNEWS_BOL_CronCommand';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_cron_command';
    }
}