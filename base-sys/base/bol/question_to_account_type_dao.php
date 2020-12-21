<?php

class BOL_QuestionToAccountTypeDao extends PEEP_BaseDao
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
     * @var BOL_QuestionToAccountTypeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionToAccountTypeDao
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
        return 'BOL_QuestionToAccountType';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_to_account_type';
    }

    public function findQuestionsForAccountType( $accountType )
    {
        $sql = ' SELECT `question`.*  FROM  ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `question`
                    INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`questionName` = `question`.`name` )
                    INNER JOIN ' . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . ' as `account` ON ( `account`.`name` = `atq`.`accountType` )
                WHERE  `atq`.`accountType` = :accountType ';

        return $this->dbo->queryForObjectList($sql, BOL_QuestionDao::getInstance()->getDtoClassName(), array('accountType' => $accountType));
    }
    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    /* public function findAccountTypesByQuestionName( $questionName )
    {
        $sql = ' SELECT `account`.*, COUNT( `questions`.`id` ) AS `questionCount` FROM  ' . BOL_QuestionAccountType::getInstance()->getDtoClassName() . ' as `account`
                            INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`accountType` = `account`.`name` )
                            INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`questionName` = `question`.`name` )
                    WHERE  `atq`.`questionName` = :questionName ';

        return $this->dbo->queryForList($sql, BOL_QuestionAccountType::getInstance()->getDtoClassName(), array('questionName' => $questionName));
    } */

    public function findByAccountType($accountType)
    {
        if ( empty($accountType) )
        {
            return null;
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('accountTYpe', $accountType);

        return $this->findListByExample($ex);
    }

    public function findByQuestionName($questionName)
    {
        if ( empty($questionName) )
        {
            return array();
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('questionName', $questionName);

        return $this->findListByExample($ex);
    }

    public function deleteByQuestionName($questionName)
    {
        if ( empty($questionName) )
        {
            return;
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('questionName', $questionName);

        return $this->deleteByExample($ex);
    }

    public function deleteByAccountType($accountType)
    {
        if ( empty($accountType) )
        {
            return null;
        }

        $ex = new PEEP_Example();
        $ex->andFieldEqual('accountType', $accountType);

        return $this->deleteByExample($ex);
    }
    
    public function findByAccountTypeAndQuestionNameList( $accountType, array $questionNameList )
    {
        if ( empty($accountType) || empty($questionNameList) || !is_array($questionNameList) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('accountType', $accountType);
        $example->andFieldInArray('questionName', $questionNameList);

        return $this->findListByExample($example);
    }

    public function deleteByQuestionNameAndAccountTypeList( $questionName, array $accountTypeList )
    {
        if ( empty($questionName) || empty($accountTypeList) || !is_array($accountTypeList) )
        {
            return;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldInArray('accountType', $accountTypeList);

        $this->deleteByExample($example);
    }
    
    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }
}
