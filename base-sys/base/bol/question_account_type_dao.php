<?php

class BOL_QuestionAccountTypeDao extends PEEP_BaseDao
{
    const NAME = 'name';

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
     * @var BOL_QuestionAccountTypeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionAccountTypeDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_QuestionAccountType';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_account_type';
    }

    public function getDefaultAccountType()
    {
        $sql = ' SELECT `account`.*  FROM  ' . $this->getTableName() . ' as `account`
                    ORDER BY `account`.`sortOrder` LIMIT 1';

        return $this->dbo->queryForObject($sql, $this->getDtoClassName());
    }
    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function findAllAccountTypesWithQuestionsCount()
    {
        $sql = ' SELECT `account`.*, COUNT( `questions`.`id` ) AS `questionCount` FROM  ' . $this->getTableName() . ' as `account`
                            INNER JOIN ' . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . ' as `qta` ON( `account`.`name` = `qta`.`accountType` )
                            INNER JOIN ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `questions` ON( `qta`.`questionName` = `questions`.`name` )
                    GROUP BY `account`.`id`, `account`.`name`
                    ORDER BY `account`.`sortOrder` ';

        return $this->dbo->queryForList($sql);
    }

    public function findCountExlusiveQuestionForAccount($accountType)
    {
        $sql = ' SELECT COUNT( `questions`.`id` ) AS `questionCount`
            FROM ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `questions`
                    INNER JOIN ' . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . ' as `qta` ON( `questions`.`name` = `qta`.`questionName` )
                    
            WHERE `qta`.`accountType` = :accountType ';

        return $this->dbo->queryForColumn($sql, array('accountType' => $accountType));
    }

    public function findAccountTypeByNameList( array $accountTypeNameList )
    {
        if ( $accountTypeNameList === null || !is_array($accountTypeNameList) || count($accountTypeNameList) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('name', $accountTypeNameList);
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findAllAccountTypes()
    {
        $example = new PEEP_Example();
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findLastAccountTypeOrder()
    {
        $sql = " SELECT MAX( `sortOrder` ) FROM `" . $this->getTableName() . "` ";

        return $this->dbo->queryForColumn($sql);
    }

    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }
    
    public function deleteRoleByAccountType( BOL_QuestionAccountType $accountType )
    {        
        if ( empty($accountType) )
        {
            return;
        }
        
        $sql = " DELETE r FROM `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` r "
                . " INNER JOIN  " . BOL_UserDao::getInstance()->getTableName() . " u ON ( u.id = r.userId ) "
                . " INNER JOIN " . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . " `accountType` ON ( accountType.name = u.accountType ) "
                . " WHERE  u.accountType = :account AND r.roleId = :role ";
        
        $this->dbo->query($sql, array('account' => $accountType->name, 'role' => $accountType->roleId));
    }
    
    public function addRoleByAccountType( BOL_QuestionAccountType $accountType )
    {        
        if ( empty($accountType) )
        {
            return;
        }
        
        $sql = " REPLACE INTO `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` ( `userId`, `roleId` ) "
                . "SELECT u.id, :role FROM " . BOL_UserDao::getInstance()->getTableName() . " u "
                . " INNER JOIN " . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . " `accountType` ON ( accountType.name = u.accountType ) "
                . " WHERE  u.accountType = :account ";
        
        $this->dbo->query( $sql, array( 'account' => $accountType->name, 'role' => $accountType->roleId ) );
    }
}
