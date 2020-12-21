<?php

class BOL_LanguageDao extends PEEP_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var BOL_LanguageDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageDao
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Language';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_language';
    }

    /**
     * Enter description here...
     *
     * @param string $tag
     * @return BOL_Language
     */
    public function findByTag( $tag )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('tag', trim($tag));

        return $this->findObjectByExample($example);
    }

    public function findMaxOrder()
    {
        return $this->dbo->queryForColumn('SELECT MAX(`order`) FROM ' . $this->getTableName());
    }

    public function getCurrent()
    {
        $ex = new PEEP_Example();

        $ex->setOrder('`order` ASC')->setLimitClause(0, 1);

        return $this->findObjectByExample($ex);
    }

    public function countActiveLanguages()
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('status', 'active');

        return $this->countByExample($ex);
    }

    public function findActiveList()
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('status', 'active');

        return $this->findListByExample($ex);
    }
}