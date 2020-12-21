<?php

class BOL_LanguageKeyDao extends PEEP_BaseDao
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
     * @var BOL_LanguageKeyDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageKeyDao
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
        return 'BOL_LanguageKey';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_language_key';
    }

    public function findAllWithValues( $langId )
    {
        $keyTable = $this->getTableName();
        $prefixTable = BOL_LanguagePrefixDao::getInstance()->getTableName();
        $valueTable = BOL_LanguageValueDao::getInstance()->getTableName();
        $sql = 'SELECT k.*, p.`prefix`, v.`value` FROM ' . $keyTable . ' AS k
                    INNER JOIN ' . $prefixTable . ' AS p ON k.prefixId = p.id
                    INNER JOIN ' . $valueTable . ' AS v ON k.id = v.keyId AND v.languageId = ?';

        return $this->dbo->queryForList($sql, array($langId));
    }

    public function countKeyByPrefix( $prefixId )
    {
        $ex = new PEEP_Example();

        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->countByExample($ex);
    }

    public function findKeyId( $prefixId, $key )
    {

        $query = "SELECT `id` FROM `{$this->getTableName()}` WHERE `prefixId` = ? AND `key` = ? LIMIT 1";

        return $this->dbo->queryForColumn($query, array($prefixId, $key));
    }

    public function findMissingKeys( $languageId, $first, $count )
    {
        $query = "
                SELECT k.`key`,
                       `p`.`label`, `p`.`prefix`
                FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as k
                LEFT JOIN `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as v
                     ON( k.id = v.keyId  and v.`languageId` = ? )
                INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as p
                      ON(k.`prefixId` = p.id)
                WHERE v.keyId IS NULL OR (`v`.`value` IS NOT NULL AND LENGTH(`v`.`value`) = 0 )  
                LIMIT ?, ?
			";

        return $this->dbo->queryForList($query, array($languageId, $first, $count));
    }

    public function findMissingKeyCount( $languageId )
    {
        $query = "
                SELECT COUNT(*)
                FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as k
                LEFT JOIN `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as v
                     ON( k.id = v.keyId  and v.`languageId` = ? )
                INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as p
                      ON(k.`prefixId` = p.id)
                WHERE v.keyId IS NULL OR (`v`.`value` IS NOT NULL AND LENGTH(`v`.`value`) = 0 )
			";

        return $this->dbo->queryForColumn($query, array($languageId));
    }

    public function findAllPrefixKeys( $prefixId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->findListByExample($ex);
    }

    public function countAllPrefixKeys( $prefixId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->countByExample($ex);
    }
}