<?php

class BOL_QuestionValueDao extends PEEP_BaseDao
{
    const QUESTION_NAME = 'questionName';
    const VALUE = 'value';
    const SORT_ORDER = 'sortOrder';

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
     * @var BOL_QuestionValueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionValueDao
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
        return 'BOL_QuestionValue';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_value';
    }

    public function findQuestionsValuesByQuestionNameList( array $questionNameList )
    {
        if ( isset($questionNameList) && count($questionNameList) > 0 )
        {
            $list = array();

            $questionList = BOL_QuestionDao::getInstance()->findQuestionByNameList($questionNameList);
            $parentList = array();

            foreach ( $questionList as $question )
            {
                $parentList[$question->parent] = $question->parent;
                $list[$question->name] = $question->name;
            }

            $parentQuestionList = BOL_QuestionDao::getInstance()->findQuestionByNameList($parentList);
            $parentQuestions = array();
            
            foreach ( $parentQuestionList as $question )
            {
                $parentQuestions[$question->name] = $question->name;
            }

            foreach ( $parentList as $key => $value )
            {
                if ( !empty($parentQuestions[$value]) )
                {
                    $list[$value] = $value;
                }
            }

            $example = new PEEP_Example();
            $example->andFieldInArray('questionName', $list);
            $example->setOrder('questionName, sortOrder');
            $values = $this->findListByExample($example);

            $result = array();
            $questionName = '';
            $count = 0;

            foreach ( $values as $key => $value )
            {
                if ( $questionName !== $value->questionName )
                {
                    if ( !empty($questionName) )
                    {
                        $result[$questionName]['count'] = $count;
                        $count = 0;
                    }

                    $questionName = $value->questionName;
                }

                $result[$value->questionName]['values'][] = $value;
                $count++;
            }

            foreach ( $questionList as $question )
            {
                if ( !empty($question->parent) && !empty( $parentQuestions[$question->parent] ) )
                {
                    $result[$question->name]['values'] = empty($result[$question->parent]['values']) ? array() : $result[$question->parent]['values'];
                }
            }

            if ( !empty($questionName) )
            {
                $result[$questionName]['count'] = $count;
            }

            return $result;
        }

        return array();
    }

    public function findQuestionValues( $questionName )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $result = $this->findQuestionsValuesByQuestionNameList(array($questionName));
        
        if ( !empty($result[$questionName]['values']) )
        {
            return $result[$questionName]['values'];
        }
        
        return array();
    }

    public function findRealQuestionValues( $questionName )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $name = trim($questionName);

        $example = new PEEP_Example();
        $example->andFieldEqual('questionName', $name);
        $example->setOrder('sortOrder');
        $result = $this->findListByExample($example);
        
        if ( !empty($result) )
        {
            return $result;
        }
        
        return array();
    }
    
    
    public function findQuestionValue( $questionName, $value )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $name = trim($questionName);
        $valueId = (int) $value;

        $example = new PEEP_Example();
        $example->andFieldEqual('questionName', $name);
        $example->andFieldEqual('value', $valueId);
        return $this->findObjectByExample($example);
    }

    public function deleteQuestionValue( $questionName, $value )
    {
        if ( $questionName === null )
        {
            return;
        }

        $name = trim($questionName);
        $valueId = (int) $value;

        $example = new PEEP_Example();
        $example->andFieldEqual('questionName', $name);
        $example->andFieldEqual('value', $valueId);
        $this->deleteByExample($example);

        return $this->dbo->getAffectedRows();
    }
}