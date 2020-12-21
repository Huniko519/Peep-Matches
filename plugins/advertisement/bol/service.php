<?php

final class ADS_BOL_Service
{
    const BANNER_POSITION_TOP = ADS_BOL_BannerPositionDao::POSITION_VALUE_TOP;
    const BANNER_POSITION_RIGHT = ADS_BOL_BannerPositionDao::POSITION_VALUE_RIGHT;
    const BANNER_POSITION_LEFT = ADS_BOL_BannerPositionDao::POSITION_VALUE_LEFT;
    const BANNER_POSITION_BOTTOM = ADS_BOL_BannerPositionDao::POSITION_VALUE_BOTTOM;

    private $bannerDao;
 
    private $bannerLocationDao;
 
    private $bannerPositionDao;

    private $locationEnabled;

    private function __construct()
    {
        $this->bannerDao = ADS_BOL_BannerDao::getInstance();
        $this->bannerLocationDao = ADS_BOL_BannerLocationDao::getInstance();
        $this->bannerPositionDao = ADS_BOL_BannerPositionDao::getInstance();

        $this->locationEnabled = BOL_GeolocationService::getInstance()->isServiceAvailable();
    }

    private static $classInstance;

 
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getLocationEnabled()
    {
        return (bool) $this->locationEnabled;
    }


    public function saveBanner( ADS_BOL_Banner $dto )
    {
        $this->bannerDao->save($dto);
    }


    public function saveBannerPosition( ADS_BOL_BannerPosition $dto )
    {
        $this->bannerPositionDao->save($dto);
    }


    public function saveBannerLocation( ADS_BOL_BannerLocation $dto )
    {
        $this->bannerLocationDao->save($dto);
    }


    public function findAllBanners()
    {
        return $this->bannerDao->findAll();
    }

    public function resetBannersForPlugin( $position, $pluginKey )
    {
        $this->bannerPositionDao->deleteByPositionAndPluginKey($position, $pluginKey);
    }

    public function findBannersCount( $position, $pluginKey )
    {
        return $this->bannerPositionDao->findBannersCount($position, $pluginKey);
    }

    public function findBannerIdList( $position, $pluginKey )
    {
        $banners = $this->bannerPositionDao->findBannerList($position, $pluginKey);
        $idList = array();

 
        foreach ( $banners as $banner )
        {
            $idList[] = $banner->getBannerId();
        }

        return $idList;
    }

    public function findAllBannersInfo()
    {
        $info = $this->bannerDao->findAllBannersInfo();
        $resultArray = array();

        foreach ( $info as $infoItem )
        {
            if ( !isset($resultArray[$infoItem['id']]) )
            {
                $resultArray[$infoItem['id']] = array();
                $resultArray[$infoItem['id']] = array('label' => $infoItem['label'], 'code' => $infoItem['code']);
            }

            if ( $infoItem['location'] !== null )
            {
                if ( !isset($resultArray[$infoItem['id']]['location']) )
                {
                    $resultArray[$infoItem['id']]['location'] = array();
                }

                $resultArray[$infoItem['id']]['location'][$infoItem['location']] = BOL_GeolocationService::getInstance()->getCountryNameForCC3($infoItem['location']);
            }
        }

        return $resultArray;
    }


    public function findBannerById( $id )
    {
        return $this->bannerDao->findById($id);
    }

    public function findBannerLocations( $bannerId )
    {
        return $this->bannerLocationDao->findListByBannerId($bannerId);
    }

    public function resetBannerLocations( $bannerId )
    {
        $this->bannerLocationDao->deleteByBannerId($bannerId);
    }

    public function deleteBanner( $bannerId )
    {
        $this->bannerDao->deleteById($bannerId);
        $this->bannerLocationDao->deleteByBannerId($bannerId);
        $this->bannerPositionDao->deleteByBannerId($bannerId);
    }

    public function findPlaceBannerList( $pluginKey, $position, $location = null )
    {
        $event = new BASE_CLASS_EventCollector('ads.enabled_plugins');
        PEEP::getEventManager()->trigger($event);

        $pluginList = $event->getData();

        $banners = $this->bannerDao->findPlaceBannerList($pluginKey, $position, $location);

        $plugin = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);

        if ( empty($banners) && $pluginKey !== 'base' && $plugin !== null && in_array($plugin->getKey(), $pluginList) )
        {
            $banners = $this->bannerDao->findPlaceBannerList('base', $position, $location);
        }

        return $banners;
    }
}
