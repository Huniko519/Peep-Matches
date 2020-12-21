<?php
class BOL_AuthorizationModeratorDao extends PEEP_BaseDao
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
     * @var BOL_AuthorizationModeratorDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationModeratorDao
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
        return 'BOL_AuthorizationModerator';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_authorization_moderator';
    }

    public function getIdByUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->findIdByExample($ex);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION));
    }

    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        $example = new PEEP_Example();
        $example->setOrder('id');

        return $this->findListByExample($example, 3600 * 24, array(
            BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION,
            PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD
        ));
    }
}