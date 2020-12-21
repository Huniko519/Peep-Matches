<?php

class BOL_PreferenceSectionDao extends PEEP_BaseDao
{
    const NAME = 'name';
    const SECTION = 'sectionName';

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_PreferenceSectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PreferenceSectionDao
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
        return 'BOL_PreferenceSection';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_preference_section';
    }

    /**
     *
     * @return BOL_PreferenceSection
     */
    public function findAllSections()
    {
        $example = new PEEP_Example();
        $example->setOrder(" sortOrder ");

        return $this->findListByExample($example);
    }

    /**
     *
     * @return BOL_PreferenceSection
     */
    public function findSection( $name )
    {
        if ( empty($name) )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::NAME, $name);

        return $this->findObjectByExample($example);
    }

    public function deleteSection( $name )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('name', $name);
        $this->deleteByExample($example);
        
        return $this->dbo->getAffectedRows();
    }

}