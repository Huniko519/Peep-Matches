<?php

class NOTIFICATIONS_BOL_ScheduleDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_ScheduleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_ScheduleDao
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
        return 'NOTIFICATIONS_BOL_Schedule';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'notifications_schedule';
    }

    /**
     *
     * @param int $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }
}