<?php

class PVISITORS_CTRL_List extends PEEP_ActionController
{
    public function index( array $params )
    {
        if ( !$userId = PEEP::getUser()->getId() )
        {
            throw new AuthenticateException();
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $lang = PEEP::getLanguage();

        $perPage = (int)PEEP::getConfig()->getValue('base', PEEP::getPluginManager()->isPluginActive('peepsys') ? 'users_on_page' : 'users_count_on_page');
        $visitors = PVISITORS_BOL_Service::getInstance()->findVisitorsForUser($userId, $page, $perPage);

        $visitorList = array();
        if ( $visitors )
        {
        	foreach ( $visitors as $visitor )
        	{
        		$visitorList[$visitor->visitorId] = array('last_visit' => $lang->text('pvisitors', 'visited') . ' ' . '<span class="peep_remark">' . $visitor->visitTimestamp . '</span>');
        	}
	        $itemCount = PVISITORS_BOL_Service::getInstance()->countVisitorsForUser($userId);

            if ( PEEP::getPluginManager()->isPluginActive('peepsys') )
            {
            $cmp = PEEP::getClassInstance('BASE_CMP_Users', $visitorList, array(), $itemCount);
            }
            else
            {
                $visitorsUsers = PVISITORS_BOL_Service::getInstance()->findVisitorUsers($userId, $page, $perPage);
                $cmp = new PVISITORS_CMP_Users($visitorsUsers, $itemCount, $perPage, true, $visitorList);
            }
	        $this->addComponent('visitors', $cmp);
        }
        else 
        {
        	$this->assign('visitors', null);
        }
        
        $this->setPageHeading($lang->text('pvisitors', 'viewed_profile'));
        $this->setPageTitle($lang->text('pvisitors', 'viewed_profile'));

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'dashboard');
    }
}