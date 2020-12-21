<?php

class BOL_LanguageValueDao extends PEEP_BaseDao
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
     * @var BOL_LanguageValueDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageValueDao
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
        return 'BOL_LanguageValue';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_language_value';
    }

    public function findLastKeyList( $first, $count, $prefix = null )
    {
        if ( $prefix !== null )
        {
            $prefixId = BOL_LanguagePrefixDao::getInstance()->findPrefixId($prefix);

            if ( !$prefixId )
            {
                throw new Exception('There is no such prefix..');
            }
        }

        $query_part = array();

        $query_part['optional-prefix_criteria'] = ( $prefix !== null && $prefixId > 0 ) ? "`p`.`id` = {$prefixId}" : '1';

        $query_part['dev-mode-order'] = !$this->isDevMode() && $prefix == null ? "IF(`p`.`prefix` = 'peep_custom', 1, 0) DESC, " : '';

        $keyTable = BOL_LanguageKeyDao::getInstance()->getTableName();
        $prefixTable = BOL_LanguagePrefixDao::getInstance()->getTableName();

        $query = "
		SELECT `key`,
		       `p`.`label`, `p`.`prefix`
		FROM `" . $keyTable . "` as `k`
		INNER JOIN `" . $prefixTable . "` AS `p`
		     ON ( `k`.`prefixId` = `p`.`id` )
	    WHERE {$query_part['optional-prefix_criteria']} /*optional-prefix_criteria*/ 
		ORDER BY {$query_part['dev-mode-order']} `p`.`label`,
		         `k`.`id` desc
		LIMIT ?, ?
		";

        return $this->dbo->queryForList($query, array($first, $count));
    }

    public function findSearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ?
			 ORDER BY `p`.`label`,
			        `k`.`id` desc
			 LIMIT ?, ?
			";

        return $this->dbo->queryForList($_query, array("%{$search}%", $languageId, $first, $count));
    }

    public function findKeySearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` ) 
			 WHERE `k`.`key` LIKE :keySearch
			 LIMIT :first, :count
			";

        return $this->dbo->queryForList($_query, array('keySearch'=>"%{$search}%", 'first'=>$first, 'count'=>$count));
    }




    public function countSearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ? 
			";

        return $this->dbo->queryForColumn($_query, array("%{$search}%", $languageId));
    }

    public function countKeySearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `k`.`key` LIKE :keySearch
			";

        return $this->dbo->queryForColumn($_query, array('keySearch'=>"%{$search}%"));
    }

    public function findValue( $languageId, $keyId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('languageId', $languageId)->andFieldEqual('keyId', $keyId);

        return $this->findObjectByExample($ex);
    }

    public function deleteValues( $languageId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('languageId', $languageId);

        $this->deleteByExample($ex);
    }

    private function isDevMode()
    {
        if ( !empty($_GET) )
        {
            $arr = explode('?', PEEP::getRequest()->getRequestUri());

            return $arr[0] == PEEP::getRouter()->uriForRoute('admin_developer_tools_language');
        }

        return PEEP::getRequest()->getRequestUri() == PEEP::getRouter()->uriForRoute('admin_developer_tools_language');
    }

    public function deleteByKeyId( $id )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('keyId', $id);

        $this->deleteByExample($ex);
    }
}