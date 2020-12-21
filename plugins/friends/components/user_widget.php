<?php

class FRIENDS_CMP_UserWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        $service = FRIENDS_BOL_Service::getInstance();
        $userId = $params->additionalParamList['entityId'];
        $count = (int) $params->customParamList['count'];

        $idList = $service->findUserFriendsInList($userId, 0, $count);
        $total = $service->countFriends($userId);
        $userService = BOL_UserService::getInstance();

        $eventParams =  array(
                'action' => 'friends_view',
                'ownerId' => $userId,
                'viewerId' => PEEP::getUser()->getId()
            );
        
        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        if ( empty($idList) && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        if( !empty($idList) )
        {
            $this->addComponent('userList', new BASE_CMP_AvatarUserList($idList));
        }

        $username = BOL_UserService::getInstance()->getUserName($userId);

        $toolbar = array();
        
        if ( $total > $count )
        {
            $toolbar = array(array('label'=>PEEP::getLanguage()->text('base','view_all'), 'href'=>PEEP::getRouter()->urlForRoute('friends_user_friends', array('user'=>$username))));
        }

        $this->assign('toolbar', $toolbar);
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => PEEP::getLanguage()->text('friends', 'user_widget_settings_count'),
            'value' => '6'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('friends', 'user_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}