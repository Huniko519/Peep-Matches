<?php

class BOL_AuthorizationActionDao extends PEEP_BaseDao
{
    const CACHE_TAG_AUTHORIZATION = 'base.auth.page_load_items';

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
     * @var BOL_AuthorizationActionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationActionDao
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
        return 'BOL_AuthorizationAction';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_authorization_action';
    }

    public function getIdByName( $name )
    {
        if ( $name === null )
        {
            return null;
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('name', $name);

        return $this->findIdByExample($ex);
    }

    public function findActionListByGroupId( $groupId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('groupId', $groupId);

        return $this->findListByExample($ex);
    }

    /**
     * 
     * @param $name
     * @return BOL_AuthorizationAction
     */
    public function findAction( $actionName, $groupId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('name', $actionName);
        $ex->andFieldEqual('groupId', $groupId);

        return $this->findObjectByExample($ex);
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