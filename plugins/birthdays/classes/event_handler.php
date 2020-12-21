<?php


class BIRTHDAYS_CLASS_EventHandler
{

    public function __construct()
    {
        
    }

    public function addUserlistData( BASE_CLASS_EventCollector $event )
    {
        $event->add(
            array(
                'label' => PEEP::getLanguage()->text('base', 'user_list_menu_item_birthdays'),
                'url' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays')),
                'iconClass' => 'peep_ic_calendar',
                'key' => 'birthdays',
                'order' => 5,
                'dataProvider' => array(BIRTHDAYS_BOL_Service::getInstance(), 'getUserListData')
            )
        );
    }

    public function privacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $action = array(
            'key' => 'birthdays_view_my_birthdays',
            'pluginKey' => 'birthdays',
            'label' => $language->text('birthdays', 'privacy_action_view_my_birthday'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function onTodayBirthday( PEEP_Event $e )
    {
        $params = $e->getParams();
        $userIds = $params['userIdList'];
        $usersData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

        $actionParams = array(
            'entityType' => 'birthday',
            'pluginKey' => 'birthdays',
            'replace' => true
        );
        $actionData = array(
            'time' => time(),     
        );
        
        $birthdays = BOL_QuestionService::getInstance()->getQuestionData($userIds, array('birthdate'));
            
        foreach ( $userIds as $userId )
        {
            $userEmbed = '<a href="' . $usersData[$userId]['url'] . '">' . $usersData[$userId]['title'] . '</a>';
            $actionParams['userId'] = $userId;
            $actionParams['entityId'] = $userId;
            $actionData['line'] = PEEP::getLanguage()->text('birthdays', 'feed_item_line', array('user' => $userEmbed));
            $actionData['content'] = '<div class="peep_user_list_picture">' .PEEP::getThemeManager()->processDecorator('avatar_item', $usersData[$userId]) . '</div>';
            
            if ( !empty($birthdays[$userId]['birthdate']) )
            {
                $actionData['birthdate'] = $birthdays[$userId]['birthdate'];
                $actionData['userData'] = $usersData[$userId];
            }
            
            $event = new PEEP_Event('feed.action', $actionParams, $actionData);

            PEEP::getEventManager()->trigger($event);

            BOL_AuthorizationService::getInstance()->trackActionForUser($userId, 'birthdays', 'birthday');
        }
    }

    public function onCnewsItemRender( PEEP_Event $event )
    {
        $params = $event->getParams();
        $content = $event->getData();
        
        if ( !empty($params['action']['entityType']) && !empty($params['action']['pluginKey']) && $params['action']['entityType'] == 'birthday' && $params['action']['pluginKey'] == 'birthdays' )
        {
            $html = '<div class="peep_user_list_data"></div>';
            
            if ( !empty($content['birthdate']) && !empty($content['userData']) )
            {
                $date = UTIL_DateTime::parseDate($content['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $birthdate = UTIL_DateTime::formatBirthdate($date['year'], $date['month'], $date['day']);
                
                if ( $date['month'] == intval(date('m')) )
                {
                    if ( intval(date('d')) + 1 == intval($date['day']) )
                    {
                        $birthdate = '<span class="peep_green" style="font-weight: bold; text-transform: uppercase;">' . PEEP::getLanguage()->text('base', 'date_time_tomorrow') . '</a>';
                    }
                    else if ( intval(date('d')) == intval($date['day']) )
                    {
                        $birthdate = '<span class="peep_green" style="font-weight: bold; text-transform: uppercase;">' . PEEP::getLanguage()->text('base', 'date_time_today') . '</span>';
                    }
                }
                
                $html = '<div class="peep_user_list_data">
                            <a href="'.$content['userData']["url"].'">'.$content['userData']["title"].'</a><br><span style="font-weight:normal;" class="peep_small">'. PEEP::getLanguage()->text('birthdays', 'birthday') . ' '. $birthdate . '</span>                
                         </div>';
            }
            
            $content['content'] .= $html;
            $content['content'] = '<div class="clearfix">'.$content['content'].'</div>';
            
            $event->setData($content);
        }
    }
    
    public function onChangePrivacy( PEEP_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        $actionList = $params['actionList'];

        if ( empty($actionList['birthdays_view_my_birthdays']) )
        {
            return;
        }

        $privacyDto = BIRTHDAYS_BOL_Service::getInstance()->findBirthdayPrivacyByUserId($userId);

        if ( empty($privacyDto) )
        {
            $privacyDto = new BIRTHDAYS_BOL_Privacy();
            $privacyDto->userId = $userId;
        }

        $privacyDto->privacy = $actionList['birthdays_view_my_birthdays'];

        BIRTHDAYS_BOL_Service::getInstance()->saveBirthdayPrivacy($privacyDto);
    }

    public function onUserUnregister( PEEP_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];
        BIRTHDAYS_BOL_Service::getInstance()->deleteBirthdayPrivacyByUserId($userId);
    }

    public function feedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $event->add(array(
            'label' => $language->text('birthdays', 'feed_content_label'),
            'activity' => '*:birthday'
        ));
    }

    public function feedComment( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'birthday' )
        {
            return;
        }

        $userId = (int) $params['entityId'];

        if ( $userId == $params['userId'] )
        {
            $string = PEEP::getLanguage()->text('birthdays', 'feed_activity_self_birthday_string');
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $string = PEEP::getLanguage()->text('birthdays', 'feed_activity_birthday_string', array(
                    'user' => $userEmbed
                ));
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $params['commentId'],
                'entityId' => $userId,
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'birthdays'
                ), array(
                'string' => $string,
                'line' => null
            )));

        if ( $userId != $params['userId'] )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($params['userId']);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($params['userId']);
            
            $urlContent = PEEP::getEventManager()->call('feed.get_item_permalink', array('entityId' => $userId, 'entityType' => $params['entityType']));
            
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $contentImage = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $avatar = $avatars[$params['userId']];

            $event = new PEEP_Event('notifications.add', array(
                    'pluginKey' => 'birtdays',
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId'],
                    'action' => 'comment',
                    'userId' => $params['entityId'],
                    'time' => time()
                    ), array(
                    'avatar' => $avatar,
                    'string' => array(
                        'key' => 'birthdays+console_notification_comment',
                        'vars' => array(
                            'userName' => $userName,
                            'userUrl' => $userUrl
                        )
                    ),
                    'content' => strip_tags($data['message']),
                    'url' => $urlContent
                ));



            PEEP::getEventManager()->trigger($event);
        }
    }

    public function feedLike( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'birthday' )
        {
            return;
        }

        $userId = (int) $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        $string = PEEP::getLanguage()->text('birthdays', 'feed_activity_birthday_string_like', array('user' => $userEmbed));

        if ( $userId == PEEP::getUser()->getId() )
        {
            $string = PEEP::getLanguage()->text('birthdays', 'feed_activity_birthday_string_like_own', array('user' => $userEmbed));
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $userId,
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'birthdays'
                ), array(
                'string' => $string,
                'line' => null
            )));

        if ( $userId != PEEP::getUser()->getId() )
        {
            $userName = BOL_UserService::getInstance()->getDisplayName(PEEP::getUser()->getId());
            $userUrl = BOL_UserService::getInstance()->getUserUrl(PEEP::getUser()->getId());

            $contentUrl = PEEP::getEventManager()->call('feed.get_item_permalink', array('entityId' => $userId, 'entityType' => $params['entityType']));
            
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']), true, true, false, false);
            $avatar = $avatars[$params['userId']];

            $event = new PEEP_Event('notifications.add', array(
                    'pluginKey' => 'birtdays',
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId'],
                    'action' => 'like',
                    'userId' => $params['entityId'],
                    'time' => time()
                    ), array(
                    'avatar' => $avatar,
                    'string' => array(
                        'key' => 'birthdays+console_notification_like',
                        'vars' => array(
                            'userName' => $userName,
                            'userUrl' => $userUrl
                        )
                    ),
                    'url' => $contentUrl,
                    //'contentImage' => $contentImage
                ));

            PEEP::getEventManager()->trigger($event);
        }
    }

    public function notificationActions( PEEP_Event $event )
    {
        $event->add(array(
            'section' => 'birthdays',
            'action' => 'comment',
            'sectionIcon' => 'peep_ic_calendar',
            'sectionLabel' => PEEP::getLanguage()->text('birthdays', 'email_notifications_section_label'),
            'description' => PEEP::getLanguage()->text('birthdays', 'email_notifications_setting_status_comment'),
            'selected' => true
        ));

        $event->add(array(
            'section' => 'birthdays',
            'action' => 'like',
            'sectionIcon' => 'peep_ic_calendar',
            'sectionLabel' => PEEP::getLanguage()->text('birthdays', 'email_notifications_section_label'),
            'description' => PEEP::getLanguage()->text('birthdays', 'email_notifications_setting_status_like'),
            'selected' => true
        ));
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind('base.add_user_list', array($this, 'addUserlistData'));
        PEEP::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'privacyAddAction'));
        PEEP::getEventManager()->bind('birthdays.today_birthday_user_list', array($this, 'onTodayBirthday'));
        PEEP::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangePrivacy'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        PEEP::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'feedCollectConfigurableActivity'));
        PEEP::getEventManager()->bind('feed.after_comment_add', array($this, 'feedComment'));
        PEEP::getEventManager()->bind('feed.after_like_added', array($this, 'feedLike'));
        PEEP::getEventManager()->bind('notifications.collect_actions', array($this, 'notificationActions'));

        $credits = new BIRTHDAYS_CLASS_Credits();
        PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        
        PEEP::getEventManager()->bind('feed.on_item_render', array($this, "onCnewsItemRender"));
    }

    public function init()
    {
        
    }
}
