<?php

class ADS_BOL_BannerDao extends PEEP_BaseDao
{
    const LABEL = 'label';
    const CODE = 'code';
    const CACHE_TAG_ADS_BANNERS = 'ads.position_banner_cache';

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
        return 'ADS_BOL_Banner';
    }

    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'ads_banner';
    }

    public function findAllBannersInfo()
    {
        $query = "SELECT `b`.*, `l`." . ADS_BOL_BannerLocationDao::LOCATION . " FROM `" . $this->getTableName() . "` AS `b`
            LEFT JOIN `" . ADS_BOL_BannerLocationDao::getInstance()->getTableName() . "` AS `l` ON ( `b`.`id` = `l`.`" . ADS_BOL_BannerLocationDao::BANNER_ID . "` )";

        return $this->dbo->queryForList($query);
    }

    public function findPlaceBannerList( $pluginKey, $position, $location = null )
    {
        $query = "SELECT `b`.* FROM `" . $this->getTableName() . "` AS `b`
            LEFT JOIN `" . ADS_BOL_BannerPositionDao::getInstance()->getTableName() . "` AS `bp` ON (`b`.`id` = `bp`.`" . ADS_BOL_BannerPositionDao::BANNER_ID . "`)
            LEFT JOIN `" . ADS_BOL_BannerLocationDao::getInstance()->getTableName() . "` AS `bl` ON (`b`.`id` = `bl`.`" . ADS_BOL_BannerLocationDao::BANNER_ID . "`)
            WHERE `bp`.`" . ADS_BOL_BannerPositionDao::POSITION . "` = :position AND `bp`.`" . ADS_BOL_BannerPositionDao::PLUGIN_KEY . "` IN ( :pluginKey, 'base' )
                AND ( `bl`.`" . ADS_BOL_BannerLocationDao::LOCATION . "` IS NULL" . ( $location === null ? ')' : " OR `bl`.`" . ADS_BOL_BannerLocationDao::LOCATION . "` = :location)" );

        $params = array('pluginKey' => $pluginKey, 'position' => $position);

        if ( $location != null )
        {
            $params['location'] = $location;
        }

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params, 3600 * 24, array(self::CACHE_TAG_ADS_BANNERS, PEEP_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    protected function clearCache()
    {
        PEEP::getCacheManager()->clean(array(self::CACHE_TAG_ADS_BANNERS));
    }
}