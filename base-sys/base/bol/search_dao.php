<?php

class BOL_SearchDao extends PEEP_BaseDao
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
     * @var BOL_SearchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_Search
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
        return 'BOL_Search';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_search';
    }

    public function findExpireSearchId()
    {
        $expirationTime = 60 * 60 * 24; // 1 day
        $query = "SELECT id FROM " . $this->getTableName() . " WHERE (" . $this->dbo->escapeString(time()) . " - timeStamp) > " . $this->dbo->escapeString($expirationTime);

        return $this->dbo->queryForColumnList($query);
    }
}