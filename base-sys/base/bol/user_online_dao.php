<?php

class BOL_UserOnlineDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const ACTIVITY_STAMP = 'activityStamp';
    const CONTEXT = 'context';
    const CONTEXT_VAL_DESKTOP = 1;
    const CONTEXT_VAL_MOBILE = 2;
    const CONTEXT_VAL_API = 4;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     * 
     * @var BOL_UserOnlineDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserOnlineDao
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
        return 'BOL_UserOnline';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_online';
    }

    /**
     * 
     * @param integer $userId
     * @return BOL_UserOnline
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        return $this->findObjectByExample($example);
    }

    public function findOnlineUserIdListFromIdList( $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE `" . self::USER_ID . "` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForList($query);
    }

    public function deleteExpired( $timestamp )
    {
        $query = "DELETE FROM `{$this->getTableName()}` WHERE `activityStamp` < ?";

        $this->dbo->query($query, array($timestamp));
    }
}
