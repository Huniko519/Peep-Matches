<?php

class GOOGLELOCATION_CTRL_UserList extends PEEP_ActionController
{
    public function index($params)
    {
        if ( !PEEP::getPluginManager()->isPluginActive("skadate") )
        {
            $menu = BASE_CTRL_UserList::getMenu('map');
            $this->addComponent('menu', $menu);
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
      
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        $first = $page - 1;
        $usersPerPage = (int)PEEP::getConfig()->getValue('base', 'users_count_on_page');

        $userIdList = GOOGLELOCATION_BOL_LocationService::getInstance()->getEntityListFromSession($hash);
        
        //BOL_UserService::getInstance()->findUserListByIdList($userIdList);
        $userList = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserListByCoordinates($lat, $lon, $first, $usersPerPage, $userIdList);
        $usersCount = GOOGLELOCATION_BOL_LocationService::getInstance()->findUserCountByCoordinates($lat, $lon, $userIdList);
        
        $listCmp = new GOOGLELOCATION_CMP_UserList($userList, $usersCount, $usersPerPage);
        $this->addComponent('cmp', $listCmp);
        
        $locationName = GOOGLELOCATION_BOL_LocationService::getInstance()->getLocationName($lat, $lon);
        $this->assign('locationName', $locationName);
        
        $language = PEEP::getLanguage();        
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'browse_users_page_heading'));
        $this->setPageTitle(PEEP::getLanguage()->text('base', 'browse_users_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_bookmark');
        
        $this->assign( 'backUrl', empty($_GET['backUri']) ? null : PEEP_URL_HOME. $_GET['backUri'] );
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('googlelocation')->getCtrlViewDir().'entity_list_index.html');
    }
}
