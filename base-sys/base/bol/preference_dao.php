<?php

class BOL_PreferenceDao extends PEEP_BaseDao
{
    const KEY = 'key';
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
     * @var BOL_PreferenceDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PreferenceDao
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
        return 'BOL_Preference';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_preference';
    }

    /**
     *
     * @param string $key
     * @return array <BOL_Preference>
     */
    public function findAllPreference()
    {
        $example = new PEEP_Example();
        $example->setOrder(" sortOrder ");

        return $this->findListByExample($example);
    }

    /**
     *
     * @param string $key
     * @return BOL_Preference
     */
    public function findPreference( $key )
    {
        if ( empty( $key ) )
        {
            return null;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    /**
     *
     * @param string $key
     * @return array <BOL_Preference>
     */
    public function findPreferenceList( $keyList )
    {
        if ( empty( $keyList ) || !is_array($keyList) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray(self::KEY, $keyList);

        return $this->findListByExample($example);
    }
    
    /**
     *
     * @param string $key
     * @return BOL_Preference
     */
    public function deletePreference( $key )
    {
        if ( empty( $key ) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, $key);

        $this->deleteByExample($example);
        return (boolean)$this->dbo->getAffectedRows();
    }

   /**
     *
     * @param string $section
     * @return array <BOL_Preference>
     */
    
    public function findPreferenceListBySectionName( $section )
    {
        if ( empty( $section ) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(self::KEY, $section);

        return $this->findListByExample($example);
    }
}