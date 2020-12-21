<?php

class ADS_BOL_BannerPositionDao extends PEEP_BaseDao
{
    const BANNER_ID = 'bannerId';
    const POSITION = 'position';
    const PLUGIN_KEY = 'pluginKey';

    const POSITION_VALUE_TOP = 'top';
    const POSITION_VALUE_RIGHT = 'right';
    const POSITION_VALUE_LEFT = 'left';
    const POSITION_VALUE_BOTTOM = 'bottom';

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'ADS_BOL_BannerPosition';
    }


    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'ads_banner_position';
    }

    public function deleteByPositionAndPluginKey( $position, $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::POSITION, $position);
        $example->andFieldEqual(self::PLUGIN_KEY, $pluginKey);
        $this->deleteByExample($example);
    }

    public function findBannersCount( $position, $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::POSITION, $position);
        $example->andFieldEqual(self::PLUGIN_KEY, $pluginKey);

        return $this->countByExample($example);
    }

    public function findBannerList( $position, $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::POSITION, $position);
        $example->andFieldEqual(self::PLUGIN_KEY, $pluginKey);

        return $this->findListByExample($example);
    }

    public function deleteByBannerId( $bannerId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::BANNER_ID, (int) $bannerId);
        $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(ADS_BOL_BannerDao::CACHE_TAG_ADS_BANNERS));
    }
}