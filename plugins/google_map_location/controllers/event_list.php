<?php

class GOOGLELOCATION_CTRL_EventList extends PEEP_ActionController
{
    public function index($params)
    {        
        if( !PEEP::getPluginManager()->isPluginActive('event') )
        {
            throw new Redirect404Exception();
        }
        
        $lat = null; 
        $lon = null;
        $hash = null;
        
        if ( !empty($params['lat']) )
        {
            $lat = (float)$params['lat'];
        }
        
        if ( !empty($params['lat']) )
        {
            $lat = (float)$params['lat'];
        }
        
        if ( !empty($params['lng']) )
        {
            $lon = (float)$params['lng'];
        }

        if ( !empty($params['hash']) )
        {
            $hash = $params['hash'];
        }

        $entityIdList = GOOGLELOCATION_BOL_LocationService::getInstance()->getEntityListFromSession($hash);
        
        $bridge = new GOOGLELOCATION_CLASS_EventBridge();
        $listCmp = $bridge->getEventListCmp($entityIdList); 
        $this->addComponent('cmp', $listCmp);
        
        $locationName = GOOGLELOCATION_BOL_LocationService::getInstance()->getLocationName($lat, $lon);
        $this->assign('locationName', $locationName);
        
        $language = PEEP::getLanguage();        
        $this->setPageHeading(PEEP::getLanguage()->text('googlelocation', 'browse_page_heading'));
        $this->setPageTitle(PEEP::getLanguage()->text('googlelocation', 'events_browse_page_title'));
        $this->setPageHeadingIconClass('peep_ic_bookmark');
        
        $this->assign( 'backUrl', empty($_GET['backUri']) ? null : PEEP_URL_HOME. $_GET['backUri'] );
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('googlelocation')->getCtrlViewDir().'entity_list_index.html');
    }
}
