<?php

class BOL_UserBlockDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const BLOCKED_USER_ID = 'blockedUserId';
    const CACHE_TAG_BLOCKED_USER = 'base.blocked_user';
    const CACHE_LIFE_TIME = 86400; //24 hour

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
        return 'BOL_UserBlock';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_block';
    }

    /**
     * 
     * @param integer $userId
     * @return BOL_UserOnline
     */
    public function findBlockedUser( $userId, $blockedUserId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        return $this->findObjectByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function findBlockedList( $userId, $userIdList )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldInArray(self::BLOCKED_USER_ID, $userIdList);

        return $this->findListByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function deleteBlockedUser( $userId, $blockedUserId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        $this->deleteByExample($example);
    }
    
    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $this->deleteByExample($example);
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $userId);
        $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }
}