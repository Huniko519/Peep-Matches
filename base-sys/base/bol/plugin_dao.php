<?php

class BOL_PluginDao extends PEEP_BaseDao
{
    const ID = 'id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const MODULE = 'module';
    const KEY = 'key';
    const IS_SYSTEM = 'isSystem';
    const IS_ACTIVE = 'isActive';
    const VERSION = 'version';
    const UPDATE = 'update';
    const LICENSE_KEY = 'licenseKey';

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
     * @var BOL_PluginDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PluginDao
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
        return 'BOL_Plugin';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_plugin';
    }

    /**
     * Returns all active plugins.
     *
     * @return array<BOL_Plugin>
     */
    public function findActivePlugins()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::IS_ACTIVE, true);
        return $this->findListByExample($example);
    }

    /**
     * Finds plugin by key.
     * 
     * @param string $key
     * @return BOL_Plugin
     */
    public function findPluginByKey( $key )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    /**
     * Deletes plugin entry by key.
     * 
     * @param string $key
     */
    public function deletePluginKey( $key )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, $key);

        $this->deleteByExample($example);
    }

    /**
     * Returns all regular (not system plugins).
     * 
     * @return array<BOL_Plugin>
     */
    public function findRegularPlugins()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::IS_SYSTEM, 0);

        return $this->findListByExample($example);
    }

    public function findPluginsForUpdateCount()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::UPDATE, 1);

        return $this->countByExample($example);
    }

    /**
     * @return BOL_Plugin
     */
    public function findPluginForManualUpdate()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::UPDATE, 2);
        $example->andFieldEqual(self::IS_ACTIVE, 1);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
}
