<?php

class NOTIFICATIONS_BOL_SendQueueDao extends PEEP_BaseDao
{
    /**sendQueueDao
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_SendQueueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_SendQueueDao
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
        return 'NOTIFICATIONS_BOL_SendQueue';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'notifications_send_queue';
    }

    public function fillData( $period, $defaultSchedules )
    {
        $usersDao = BOL_UserDao::getInstance();
        $scheduleDao = NOTIFICATIONS_BOL_ScheduleDao::getInstance();

        $query = "REPLACE INTO " . $this->getTableName() . " (`userId`, `timeStamp`) SELECT DISTINCT u.id, UNIX_TIMESTAMP() FROM " . $usersDao->getTableName() . " u
                    LEFT JOIN " . $scheduleDao->getTableName() . " s ON u.id = s.userId
                    WHERE (IF( s.schedule IS NULL, :ds, s.schedule )=:as  AND u.activityStamp < :activityStamp ) OR IF( s.schedule IS NULL, :ds, s.schedule )=:is ORDER BY u.activityStamp DESC";

        return $this->dbo->query($query, array(
            'activityStamp' => time() - $period,
            'ds' => $defaultSchedules,
            'is' => NOTIFICATIONS_BOL_Service::SCHEDULE_IMMEDIATELY,
            'as' => NOTIFICATIONS_BOL_Service::SCHEDULE_AUTO
        ));
    }

    public function findList( $count )
    {
        $example = new PEEP_Example();
        $example->setLimitClause(0, $count);
        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }
}