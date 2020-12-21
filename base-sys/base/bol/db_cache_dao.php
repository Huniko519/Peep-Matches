<?php

class BOL_DbCacheDao extends PEEP_BaseDao
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
     * @var BOL_DbCacheDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_DbCacheDao
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
        return 'BOL_DbCache';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_db_cache';
    }

    /**
     * 
     * @param string $name
     * @return BOL_DbCache
     */
    public function findByName( $name )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('name', $name);

        return $this->findObjectByExample($example);
    }

    public function deleteExpiredList()
    {
        $example = new PEEP_Example();
        $example->andFieldLessThan('expireStamp', time());

        $this->deleteByExample($example);
    }
}