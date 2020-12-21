<?php

class BOL_QuestionDataDao extends PEEP_BaseDao
{
    const QUESTION_NAME = 'questionName';
    const USER_ID = 'userId';
    const TEXT_VALUE = 'textValue';
    const INT_VALUE = 'intValue';
    const DATE_VALUE = 'dateValue';

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
     * @var BOL_QuestionDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDataDao
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
        return 'BOL_QuestionData';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_data';
    }

    public function findByQuestionsNameList( array $questionNames, $userId )
    {
        if ( $questionNames === null || count($questionNames) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldInArray('questionName', $questionNames);
        return $this->findListByExample($example);
    }

    public function deleteByQuestionNamesList( array $questionNames )
    {
        if ( $questionNames === null || count($questionNames) === 0 )
        {
            return;
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('questionName', $questionNames);
        $this->deleteByExample($example);
    }

    /**
     * Returns questions values
     *
     * @return array
     */
    public function findByQuestionsNameListForUserList( array $questionlNameList, $userIdList )
    {
        if ( $questionlNameList === null || count($questionlNameList) === 0 )
        {
            return array();
        }

        if ( $userIdList === null || count($userIdList) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('userId', $userIdList);
        $example->andFieldInArray('questionName', $questionlNameList);

        $data = $this->findListByExample($example);

        $result = array();
        foreach ( $data as $object )
        {
            $result[$object->userId][$object->questionName] = $object;
        }

        return $result;
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }
}