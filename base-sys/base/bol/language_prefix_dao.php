<?php

class BOL_LanguagePrefixDao extends PEEP_BaseDao
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
     * @var BOL_LanguagePrefixDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguagePrefixDao
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
        return 'BOL_LanguagePrefix';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_language_prefix';
    }

    public function findAllWithKeyCount()
    {
        return $this->dbo->queryForList(
            'SELECT `p`.*, COUNT(`k`.`id`) as keyCount FROM ' . $this->getTableName()
            . ' AS `p` LEFT JOIN ' . BOL_LanguageKeyDao::getInstance()->getTableName()
            . ' AS `k` ON `p`.`id` = `k`.`prefixId` GROUP BY `k`.`prefixId` '
        );
    }

    public function findPrefixId( $prefix )
    {
        $query = "SELECT `id` FROM `" . $this->getTableName() . "` WHERE `prefix`=?";

        return $this->dbo->queryForColumn($query, array($prefix));
    }

    public function findByPrefix( $prefix )
    {
        $ex = new PEEP_Example();

        $ex->andFieldEqual('prefix', $prefix);

        return $this->findObjectByExample($ex);
    }
}
