<?php

class MAILBOX_BOL_UserLastDataDao extends PEEP_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Class instance
     *
     * @var MAILBOX_BOL_UserLastDataDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_UserLastDataDao
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
        return 'MAILBOX_BOL_UserLastData';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'mailbox_user_last_data';
    }

    /**
     * @param $userId
     * @return MAILBOX_BOL_UserLastData
     */
    public function findUserLastDataFor($userId)
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }
}
