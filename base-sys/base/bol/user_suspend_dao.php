<?php

class BOL_UserSuspendDao extends PEEP_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     * 
     * @var BOL_UserSuspendDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserSuspendDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_suspend';
    }

    public function getDtoClassName()
    {
        return 'BOL_UserSuspend';
    }

    public function findByUserId( $id )
    {
        $ex = new PEEP_Example();

        $ex->andFieldEqual('userId', $id);

        return $this->findObjectByExample($ex);
    }

    public function findSupsendStatusForUserList( $idList )
    {
        $query = "SELECT `userId` FROM `" . $this->getTableName() . "` WHERE `userId` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }
}