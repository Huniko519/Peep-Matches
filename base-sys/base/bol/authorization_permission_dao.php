<?php

class BOL_AuthorizationPermissionDao extends PEEP_BaseDao
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
     * @var BOL_AuthorizationPermissionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationPermissionDao
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
        return 'BOL_AuthorizationPermission';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_authorization_permission';
    }

    /**
     * @param $actionId
     * @param $roles
     * @throws InvalidArgumentException
     * @return
     *
     */
    public function findFirstIdForRoles( $actionId, $roles )
    {
        if ( $actionId === null || (int) $actionId < 1 )
        {
            throw new InvalidArgumentException('actionId must not be null');
        }

        if ( $roles === null || count($roles) < 1 )
        {
            return null;
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('actionId', $actionId);
        $ex->andFieldInArray('roleId', $roles);
        $ex->setLimitClause(1, 1);

        return $this->findIdByExample($ex);
    }

    public function deleteAll()
    {
        $this->clearCache();
        $this->dbo->delete('TRUNCATE TABLE ' . $this->getTableName());
    }

    /**
     *
     * @param int $roleId
     * @param int $actionId
     * @return BOL_AuthorizationPermission
     */
    public function findByRoleIdAndActionId( $roleId, $actionId )
    {
        if ( (int) $roleId < 1 || (int) $actionId < 1 )
        {
            throw new InvalidArgumentException('actionId and roleId must not be null');
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('roleId', $roleId);
        $ex->andFieldEqual('actionId', $actionId);

        return $this->findIdByExample($ex);
    }

    public function deleteByActionId( $actionId )
    {
        $this->clearCache();
        $actionId = (int)$actionId;
        $example = new PEEP_Example();
        $example->andFieldEqual('actionId', $actionId);

        $this->deleteByExample($example);
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