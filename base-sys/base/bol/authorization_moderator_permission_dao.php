<?php
class BOL_AuthorizationModeratorPermissionDao extends PEEP_BaseDao
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
     * @var BOL_AuthorizationModeratorPermissionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationModeratorPermissionDao
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
        return 'BOL_AuthorizationModeratorPermission';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_authorization_moderator_permission';
    }

    public function getId( $moderatorId, $groupId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('moderatorId', $moderatorId);
        $ex->andFieldEqual('groupId', $groupId);
        $ex->setLimitClause(1, 1);

        return $this->findIdByExample($ex);
    }

    public function deleteAll()
    {
        $this->clearCache();
        $this->dbo->delete('TRUNCATE TABLE ' . $this->getTableName());
    }

    public function findListByGroupId( $groupId )
    {
        $groupId = (int) $groupId;
        $ex = new PEEP_Example();
        $ex->andFieldEqual('groupId', $groupId);

        return $this->findListByExample($ex);
    }

    public function findListByModeratorId( $moderatorId )
    {
        if ( empty($moderatorId) )
        {
            throw new InvalidArgumentException('$moderatorId is empty');
        }

        $moderatorId = (int) $moderatorId;

        $ex = new PEEP_Example();

        $ex->andFieldEqual('moderatorId', $moderatorId);

        return $this->findListByExample($ex);
    }

    public function deleteByModeratorId( $moderatorId )
    {
        $this->clearCache();
        $ex = new PEEP_Example();
        $ex->andFieldEqual('moderatorId', $moderatorId);

        return $this->deleteByExample($ex);
    }

    public function deleteByGroupId( $groupId )
    {
        $this->clearCache();
        $groupId = (int) $groupId;
        $ex = new PEEP_Example();
        $ex->andFieldEqual('groupId', $groupId);
        $this->deleteByExample($ex);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION));
    }

    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        return parent::findAll(3600 * 24, array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION, PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }
}