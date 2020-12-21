<?php

class BOL_ThemeDao extends PEEP_BaseDao
{
    const ID = 'id';
    const NAME = 'name';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const IS_ACTIVE = 'isActive';
    const CUSTOM_CSS = 'customCss';
    const MOBILE_CUSTOM_CSS = 'mobileCustomCss';
    const CUSTOM_CSS_FILENAME = 'customCssFileName';
    const SIDEBAR_POSITION = 'sidebarPosition';
    const DEVELOPER_KEY = 'developerKey';
    const BUILD = 'build';
    const LICENSE_KEY = 'licenseKey';
    const UPDATE = 'update';


    const VALUE_SIDEBAR_POSITION_LEFT = 'left';
    const VALUE_SIDEBAR_POSITION_RIGHT = 'right';
    const VALUE_SIDEBAR_POSITION_NONE = 'none';
    const CACHE_TAG_PAGE_LOAD_THEME = 'base.themes.page_load_theme';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeDao
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
        return 'BOL_Theme';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_theme';
    }

    /**
     * Returns theme by name.
     *
     * @param string $name
     * @return BOL_Theme
     */
    public function findByName( $name )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::NAME, trim($name));
        return $this->findObjectByExample($example, 24 * 3600, array(self::CACHE_TAG_PAGE_LOAD_THEME, PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME));
    }

    public function findThemesForUpdateCount()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::UPDATE, 1);

        return $this->countByExample($example);
    }
}
