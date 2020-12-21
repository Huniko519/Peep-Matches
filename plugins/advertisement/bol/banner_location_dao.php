<?php

class ADS_BOL_BannerLocationDao extends PEEP_BaseDao
{
    const BANNER_ID = 'bannerId';
    const LOCATION = 'location';

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
        return 'ADS_BOL_BannerLocation';
    }


    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'ads_banner_location';
    }

    public function findListByBannerId( $bannerId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::BANNER_ID, (int) $bannerId);

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