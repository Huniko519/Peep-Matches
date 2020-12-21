<?php

class NOTIFICATIONS_BOL_RuleDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NOTIFICATIONS_BOL_RuleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_RuleDao
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
        return 'NOTIFICATIONS_BOL_Rule';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'notifications_rule';
    }

    /**
     * 
     * @param unknown_type $key
     * @param unknown_type $userId
     * @return unknown_type
     */
    public function findRule( $key, $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('action', $userId);
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }

    public function findRuleList( $userId, $actions = null )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        if ( !empty($actions) )
        {
            $example->andFieldInArray('action', $actions);
        }

        return $this->findListByExample($example);
    }
}