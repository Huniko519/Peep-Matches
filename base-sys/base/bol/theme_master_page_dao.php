<?php

class BOL_ThemeMasterPageDao extends PEEP_BaseDao
{
    const THEME_ID = 'themeId';
    const DOCUMENT_KEY = 'documentKey';
    const MASTER_PAGE = 'masterPage';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeMasterPageDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeMasterPageDao
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
        return 'BOL_ThemeMasterPage';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_theme_master_page';
    }

    /**
     * Returns theme master pages list for provided theme id.
     *
     * @param integer $themeId
     * @return array
     */
    public function findByThemeId( $themeId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);
        return $this->findListByExample($example, 24 * 3600, array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME, PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    /**
     * Deletes theme master pages for provided theme id.
     *
     * @param integer $themeId
     * @return integer
     */
    public function deleteByThemeId( $themeId )
    {
        $this->clearCache();
        $example = new PEEP_Example();
        $example->andFieldEqual(self::THEME_ID, (int) $themeId);
        return $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME));
    }
}
