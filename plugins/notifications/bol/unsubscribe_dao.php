<?php

class NOTIFICATIONS_BOL_UnsubscribeDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_UnsubscribeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_UnsubscribeDao
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
        return 'NOTIFICATIONS_BOL_Unsubscribe';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'notifications_unsubscribe';
    }

    /**
     * 
     * @param $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * 
     * @param $userId
     * @return NOTIFICATIONS_BOL_Schedule
     */
    public function findByCode( $code )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('code', $code);

        return $this->findObjectByExample($example);
    }

    public function deleteExpired( $timeStamp )
    {
        $example = new PEEP_Example();
        $example->andFieldLessThan('timeStamp', $timeStamp);

        $this->deleteByExample($example);
    }
}