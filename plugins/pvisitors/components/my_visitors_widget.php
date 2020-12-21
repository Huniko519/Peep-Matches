<?php

class PVISITORS_CMP_MyVisitorsWidget extends BASE_CLASS_Widget
{
 
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $count = (int) $params->customParamList['count'];
        $service = PVISITORS_BOL_Service::getInstance();
        
        $userId = PEEP::getUser()->getId();
        $visitors = $service->findVisitorsForUser($userId, 1, $count);

        if ( !$visitors )
        {
        	$this->setVisible(false);
        	return;
        }

        $userIdList = array();
        foreach ( $visitors as $visitor )
        {
        	array_push($userIdList, $visitor->visitorId);
        }
        
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        
        foreach ( $avatars as &$item )
        {
        	$item['class'] = 'peep_visitor_avatar';
        }
        
        $event = new PEEP_Event('bookmarks.is_mark', array(), $avatars);
        PEEP::getEventManager()->trigger($event);
        
        if ( $event->getData() )
        {
            $avatars = $event->getData();
        }

        $this->assign('avatars', $avatars);
        $this->assign('visitors', $visitors);

        $total = $service->countVisitorsForUser($userId);
        
        if ( $total > $count )
        {
	        $toolbar = array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('pvisitors.list')
            );
	        $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar));
        }
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => PEEP::getLanguage()->text('pvisitors', 'visitor_list_widget_settings_count'),
            'value' => '4'
        );

        return $settingList;
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => true,
        	self::SETTING_SHOW_TITLE => true,
        	
        	self::SETTING_TITLE => PEEP::getLanguage()->text('pvisitors', 'viewed_profile')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}