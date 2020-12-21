<?php

class BASE_CMP_UserDashWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $count = (int) $params->customParamList['count'];
        $language = PEEP::getLanguage();
        $userService = BOL_UserService::getInstance();

        $toolbar = array(
            'latest' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest'))
            ),
            'online' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'online'))
            ),
            'featured' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured'))
            )
        );

        $onlineUsersCount = $userService->count();

        if ( $onlineUsersCount > $count )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array($toolbar['online']));
        }

        $resultList = array(
            
            'online' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_online'),
                'menu_active' => true,
                'userIds' => $this->getIdList($userService->findOnlineList(0, $count)),
                'toolbar' => ( $userService->countOnline() > $count ? array($toolbar['online']) : false ),
            ));

       

        $event = new PEEP_Event('base.userList.onToolbarReady', array(), $resultList);
        PEEP::getEventManager()->trigger($event);

        return $event->getData();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => PEEP::getLanguage()->text('base', 'user_list_widget_settings_count'),
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'user_dash_widget_settings_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}