<?php

class BOL_QuestionSectionDao extends PEEP_BaseDao
{
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
     * @var BOL_QuestionSectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionSectionDao
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
        return 'BOL_QuestionSection';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_question_section';
    }

    public function findLastSectionOrder()
    {
        $sql = " SELECT MAX( `sortOrder`) FROM `" . $this->getTableName() . "` ";
        return $this->dbo->queryForColumn($sql);
    }

    public function findBySectionName( $sectionName )
    {
        if ( $sectionName === null || mb_strlen($sectionName) === 0 )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('name', $sectionName);
        return $this->findObjectByExample($example);
    }

    public function findBySectionNameList( array $sectionNameList )
    {
        if ( $sectionNameList === null || !is_array($sectionNameList) || count($sectionNameList) === 0 )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('name', $sectionNameList);

        return $this->findListByExample($example);
    }

    
    public function findVisibleNotDeletableSection()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldEqual('isDeletable', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->setOrder(' sortOrder ASC ');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    
    public function findPreviousSection( BOL_QuestionSection $section )
    {
        if ( $section === null )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldLessOrEqual('sortOrder', (int) $section->sortOrder);
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->andFieldNotEqual('name', $section->name);
        $example->setOrder(' sortOrder desc ');

        return $this->findObjectByExample($example);
    }

    public function findNextSection( BOL_QuestionSection $section )
    {
        if ( $section === null )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldGreaterThenOrEqual('sortOrder', $section->sortOrder);
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->andFieldNotEqual('name', $section->name);
        $example->setOrder(' sortOrder ');

        return $this->findObjectByExample($example);
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);

        return $this->dbo->getAffectedRows();
    }

    public function findSortedSectionList()
    {
        $example = new PEEP_Example();
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }
    
    public function findHiddenSections()
    {
        $example = new PEEP_Example();
        $example->andFieldInArray('isHidden', 1);

        return $this->findListByExample($example);
    }
}
