<?php

class ADS_CMP_Ads extends PEEP_Component
{

    public function __construct( $params)
    {
        parent::__construct();
        
        $adsService = ADS_BOL_Service::getInstance();

        $rhandlerAttrs = PEEP::getRequestHandler()->getHandlerAttributes();

        $pluginKey = PEEP::getAutoloader()->getPluginKey($rhandlerAttrs['controller']);
     
        if ( empty($params['position']) || PEEP::getUser()->isAuthorized('ads', 'hide_ads') )
        {
            $this->setVisible(false);
            return;
        }

        $position = trim($params['position']);

        if ( !in_array($position, array(ADS_BOL_Service::BANNER_POSITION_TOP, ADS_BOL_Service::BANNER_POSITION_RIGHT, ADS_BOL_Service::BANNER_POSITION_LEFT, ADS_BOL_Service::BANNER_POSITION_BOTTOM)) )
        {
            $this->setVisible(false);
            return;
        }

        $location = BOL_GeolocationService::getInstance()->ipToCountryCode3(PEEP::getRequest()->getRemoteAddress());
        $banners = ADS_BOL_Service::getInstance()->findPlaceBannerList($pluginKey, $params['position'], $location);
        
        if ( empty($banners) )
        {
            $this->setVisible(false);
            return;
        }

        $banner = $banners[array_rand($banners)];

        $event = new PEEP_Event('ads_get_banner_code', array('pluginKey' => $pluginKey, 'position' => $params['position'], 'location' => $location));
        $result = PEEP::getEventManager()->trigger($event);


        $data = $result->getData();

        $this->assign('code', ( empty($data) ? $banner->getCode() : $data));
        $this->assign('position', $params['position']);
    }
}