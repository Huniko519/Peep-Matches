<?php

class BOL_UserApproveDao extends PEEP_BaseDao
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
     * @var BOL_UserApproveDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserApproveDao
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
        return 'BOL_UserDisapprove';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_disapprove';
    }

    public function findByUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($ex);
    }

    public function deleteByUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->deleteByExample($ex);
    }

    public function findUnapproveStatusForUserList( $idList )
    {
        $query = "SELECT `userId` FROM `" . $this->getTableName() . "`
            WHERE `userId` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }
}