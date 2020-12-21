<?php

class BOL_ThemeControlValueDao extends PEEP_BaseDao
{
    const THEME_CONTROL_KEY = 'themeControlKey';
    const THEME_ID = 'themeId';
    const VALUE = 'value';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeControlValueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeControlValueDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_ThemeControlValue';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_theme_control_value';
    }

    public function deleteThemeControlValues( $themeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        $this->deleteByExample($example);
    }

    public function findByTcNameAndThemeId( $key, $themeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_CONTROL_KEY, $key);
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        return $this->findObjectByExample($example);
    }

    public function findByThemeId( $themeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        return $this->findListByExample($example);
    }

    public function deleteByTcNameAndThemeId( $key, $themeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_CONTROL_KEY, $key);
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);

        $this->deleteByExample($example);
    }
}
