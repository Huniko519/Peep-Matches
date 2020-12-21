<?php

class BOL_QuestionDao extends PEEP_BaseDao
{
    const NAME = 'name';
    const SECTION_NAME = 'sectionName';
    const TYPE = 'type';
    const PRESENTATION = 'presentation';
    const SORT_ORDER = 'sortOrder';
    const REQUIRED = 'required';
    const ON_JOIN = 'onJoin';
    const ON_EDIT = 'onEdit';
    const ON_SEARCH = 'onSearch';
    const ON_VIEW = 'onView';

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
     * @var BOL_QuestionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDao
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
        return 'BOL_Question';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question';
    }

    public function findQuestionByName( $questionName )
    {
        if ( $questionName === null )
        {
            return null;
        }

        $name = trim($questionName);

        $example = new PEEP_Example();
        $example->andFieldEqual('name', $name);
        return $this->findObjectByExample($example);
    }

    public function findQuestionByNameList( $questionNameList )
    {
        if ( empty($questionNameList) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('name', $questionNameList);
        $dtoList = $this->findListByExample($example);

        $result = array();

        foreach ( $dtoList as $dto )
        {
            $result[$dto->name] = $dto;
        }

        return $result;
    }

    public function findQuestionsByPresentationList( $presentation )
    {
        if ( $presentation === null || !is_array($presentation) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('presentation', $presentation);
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findQuestionsByQuestionNameList( array $questionName )
    {
        if ( $questionName === null || !is_array($questionName) || count($questionName) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('name', $questionName);
        return $this->findListByExample($example);
    }

    public function findAllQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";
        
        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findSearchQuestionsForAccountType( $accountType )
    {
        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND `question`.`onSearch` = 1  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findAllQuestionsWithSectionForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.`id`, `question`.`name`, `section`.`name` as `sectionName`, `question`.`accountTypeName`,
                            `question`.`type`, `question`.`presentation`, `question`.`required`, `question`.`onJoin`,
                            `question`.`onEdit`, `question`.`onSearch`, `question`.`onView`, `question`.`base`,
                            `question`.`removable`, `question`.`columnCount`, `question`.`sortOrder`,
                            `section`.`sortOrder` as `sectionOrder`, `question`.`parent` as `parent`
                FROM `" . $this->getTableName() . "` as `question`

                LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                        ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                LEFT JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )

                ORDER BY IF( `section`.`name` IS NULL, 0, 1 )  ASC,  `section`.`sortOrder`, `question`.`sortOrder` ";
                
        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findSignUpQuestionsForAccountType( $accountType, $baseOnly = false )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $questionAdds = "";

        if ( $baseOnly === true )
        {
            $questionAdds = ' AND `question`.`base` = 1 ';
        }

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all'  )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onJoin` = '1' " . $questionAdds . "
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findEditQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onEdit` = '1'
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findRequiredQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE `qta`.`accountType` = :accountTypeName AND `question`.`required` = '1' AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findViewQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onView` = '1' 
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findBaseSignUpQuestions()
    {
        $sql = " SELECT `question`.* FROM `" . $this->getTableName() . "` as `question` 

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    WHERE `question`.`base` = 1 AND `question`.`onJoin` = '1' AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql);
    }

    public function findLastQuestionOrder( $sectionName )
    {
        $sql = " SELECT MAX( `sortOrder`) FROM `" . $this->getTableName() . "` ";

        $result = null;
        if ( isset($sectionName) && count(trim($sectionName)) > 0 )
        {
            $sql .= ' WHERE `sectionName`= :sectionName ';
            $result = $this->dbo->queryForColumn($sql, array('sectionName' => trim($sectionName)));
        }
        else
        {
            $result = $this->dbo->queryForColumn($sql);
        }

        return $result;
    }

    public function findQuestionsBySectionNameList( array $sectionNameList )
    {
        if ( $sectionNameList === null || !is_array($sectionNameList) || count($sectionNameList) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('sectionName', $sectionNameList);

        return $this->findListByExample($example);
    }

    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function findQuestionChildren( $parentQuestionName )
    {
        if ( empty($parentQuestionName) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('parent', $parentQuestionName);
        return $this->findListByExample($example);
    }
}