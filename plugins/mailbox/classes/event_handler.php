<?php


class MAILBOX_CLASS_EventHandler
{
    const CONSOLE_ITEM_KEY = 'mailbox';

    /**
     *
     * @var MAILBOX_BOL_ConversationService
     */
    private $service;

    /**
     * @var MAILBOX_BOL_AjaxService
     */
    private $ajaxService;

    public function __construct()
    {
        $this->service = MAILBOX_BOL_ConversationService::getInstance();
        $this->ajaxService = MAILBOX_BOL_AjaxService::getInstance();
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind('ads.enabled_plugins', array($this, 'mailboxAdsEnabled'));

        $credits = new MAILBOX_CLASS_Credits();
        PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));

        PEEP::getEventManager()->bind('plugin.mailbox.on_plugin_init.handle_controller_attributes', array($this, 'onHandleControllerAttributes'));

        PEEP::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));

        PEEP::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActions'));
        PEEP::getEventManager()->bind('base.online_now_click', array($this, 'onShowOnlineButton'));
        PEEP::getEventManager()->bind('base.ping', array($this, 'onPing'));
        PEEP::getEventManager()->bind('base.ping.notifications', array($this, 'onApiPing'), 1);
        PEEP::getEventManager()->bind('mailbox.ping', array($this, 'onPing'));
        PEEP::getEventManager()->bind('mailbox.mark_as_read', array($this, 'onMarkAsRead'));
        PEEP::getEventManager()->bind('mailbox.mark_unread', array($this, 'onMarkUnread'));
        PEEP::getEventManager()->bind('mailbox.get_conversation_id', array($this, 'getConversationId'));
        PEEP::getEventManager()->bind('mailbox.delete_conversation', array($this, 'onDeleteConversation'));
        PEEP::getEventManager()->bind('mailbox.create_conversation', array($this, 'onCreateConversation'));
        PEEP::getEventManager()->bind('mailbox.authorize_action', array($this, 'onAuthorizeAction'));
        PEEP::getEventManager()->bind('mailbox.find_user', array($this, 'onFindUser'));

        if (PEEP::getPluginManager()->isPluginActive('ajaxim'))
        {
            try
            {
                BOL_PluginService::getInstance()->uninstall('ajaxim');
            }
            catch(LogicException $e)
            {

            }
        }

        if (PEEP::getPluginManager()->isPluginActive('im'))
        {
            try
            {
                BOL_PluginService::getInstance()->uninstall('im');
            }
            catch(LogicException $e)
            {

            }
        }

        PEEP::getEventManager()->bind('winks.onAcceptWink', array($this, 'onAcceptWink'));
        PEEP::getEventManager()->bind('winks.onWinkBack', array($this, 'onWinkBack'));
        PEEP::getEventManager()->bind('mailbox.get_unread_message_count', array($this, 'getUnreadMessageCount'));
        PEEP::getEventManager()->bind('mailbox.get_chat_user_list', array($this, 'getChatUserList'));
        PEEP::getEventManager()->bind('mailbox.post_message', array($this, 'postMessage'));
        PEEP::getEventManager()->bind('mailbox.post_reply_message', array($this, 'postReplyMessage'));
        PEEP::getEventManager()->bind('mailbox.get_new_messages', array($this, 'getNewMessages'));
        PEEP::getEventManager()->bind('mailbox.get_new_messages_for_conversation', array($this, 'getNewMessagesForConversation'));
        PEEP::getEventManager()->bind('mailbox.get_messages', array($this, 'getMessages'));
        PEEP::getEventManager()->bind('mailbox.get_history', array($this, 'getHistory'));
        PEEP::getEventManager()->bind('mailbox.show_send_message_button', array($this, 'showSendMessageButton'));
        PEEP::getEventManager()->bind('mailbox.get_active_mode_list', array($this, 'onGetActiveModeList'));
        PEEP::getEventManager()->bind('friends.request-accepted', array($this, 'onFriendRequestAccepted'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_LOGIN, array($this, 'resetAllUsersLastData'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_REGISTER, array($this, 'resetAllUsersLastData'));

        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($this, 'updatePlugin'));
        
        PEEP::getEventManager()->bind('base.after_avatar_update', array($this, 'onChangeUserAvatar'));
    }

    public function init()
    {
        PEEP::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'sendPrivateMessageActionTool'));

        PEEP::getEventManager()->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        PEEP::getEventManager()->bind('mailbox.send_message', array($this, 'onSendMessage'));
        PEEP::getEventManager()->bind('base.on_avatar_toolbar_collect', array($this, 'onAvatarToolbarCollect'));

        PEEP::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_MARK_CONVERSATION, array($this, 'markConversation'));
        PEEP::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_DELETE_CONVERSATION, array($this, 'deleteConversation'));

        PEEP::getEventManager()->bind('notifications.send_list', array($this, 'consoleSendList'));

        PEEP::getEventManager()->bind('base.attachment_uploaded', array($this, 'onAttachmentUpload'));

        PEEP::getEventManager()->bind('console.collect_items', array($this, 'onCollectConsoleItems'));
        PEEP::getEventManager()->bind('console.load_list', array($this, 'onLoadConsoleList'));

        PEEP::getEventManager()->bind('mailbox.renderOembed', array($this, 'onRenderOembed'));
    }

    public function updatePlugin()
    {
        if (PEEP::getConfig()->configExists('mailbox', 'updated_to_messages'))
        {
            /**
             * Update to Messages
             */
            $updated_to_messages = (int)PEEP::getConfig()->getValue('mailbox', 'updated_to_messages');

            if ($updated_to_messages === 0)
            {
                $e = new BASE_CLASS_EventCollector('usercredits.action_add');

                $actions = array();

                $mailboxEvent = new PEEP_Event('mailbox.admin.add_auth_labels');
                PEEP::getEventManager()->trigger($mailboxEvent);

                $data = $mailboxEvent->getData();
                if (!empty($data))
                {
                    foreach ($data['actions'] as $name=>$langLabel)
                    {
                        $actions[] = array('pluginKey' => 'mailbox', 'action' => $name, 'amount' => 0);
                    }

                    $deleteEvent = new BASE_CLASS_EventCollector('usercredits.action_delete');
                    $deleteEvent->add(array('pluginKey' => 'mailbox', 'action' => 'send_message'));
                    $deleteEvent->add(array('pluginKey' => 'mailbox', 'action' => 'read_message'));
                    $deleteEvent->add(array('pluginKey' => 'mailbox', 'action' => 'reply_to_message'));

                    PEEP::getEventManager()->trigger($deleteEvent);
                }
                else
                {
                    $actions[] = array('pluginKey' => 'mailbox', 'action' => 'reply_to_message', 'amount' => 0);
                    $actions[] = array('pluginKey' => 'mailbox', 'action' => 'send_chat_message', 'amount' => 0);
                    $actions[] = array('pluginKey' => 'mailbox', 'action' => 'read_chat_message', 'amount' => 0);
                    $actions[] = array('pluginKey' => 'mailbox', 'action' => 'reply_to_chat_message', 'amount' => 0);
                }

                foreach ( $actions as $action )
                {
                    $e->add($action);
                }

                PEEP::getEventManager()->trigger($e);

                PEEP::getConfig()->saveConfig('mailbox', 'updated_to_messages', 1);
            }
        }

        if (PEEP::getConfig()->configExists('mailbox', 'install_complete'))
        {
            $installComplete = (int)PEEP::getConfig()->getValue('mailbox', 'install_complete');

            if (!$installComplete)
            {
                $groupName = 'mailbox';
                $authorization = PEEP::getAuthorization();
                $authorization->addGroup($groupName, 0);

                $mailboxEvent = new PEEP_Event('mailbox.admin.add_auth_labels');
                PEEP::getEventManager()->trigger($mailboxEvent);

                $data = $mailboxEvent->getData();
                if (!empty($data))
                {
                    foreach ($data['actions'] as $name=>$langLabel)
                    {
                        $authorization->addAction($groupName, $name);
                    }
                }
                else
                {
                    $authorization->addAction($groupName, 'read_message');
                    $authorization->addAction($groupName, 'send_message');
                    $authorization->addAction($groupName, 'reply_to_message');

                    $authorization->addAction($groupName, 'read_chat_message');
                    $authorization->addAction($groupName, 'send_chat_message');
                    $authorization->addAction($groupName, 'reply_to_chat_message');
                }

                PEEP::getConfig()->saveConfig('mailbox', 'install_complete', 1);

            }
        }
    }

    public function sendPrivateMessageActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( PEEP::getUser()->getId() == $userId )
        {
            return;
        }

        $activeModeList = $this->service->getActiveModeList();
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        if (!$mailModeEnabled)
        {
            if (!$chatModeEnabled)
            {
                return;
            }
            else
            {

                if ( !PEEP::getUser()->isAuthorized('mailbox', 'send_chat_message') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'send_chat_message');
                    if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
                    {
                        $linkId = 'mb' . rand(10, 1000000);
                        $linkSelector = '#' . $linkId;
                        $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');

                });', array('linkSelector'=>$linkSelector));

                        PEEP::getDocument()->addOnloadScript($script);

                        $resultArray = array(
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => PEEP::getLanguage()->text('mailbox', 'send_message'),
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
                        );

                        $event->add($resultArray);
                    }

                    return;
                }

                $checkResult = $this->service->checkUser(PEEP::getUser()->getId(), $userId);

                if (!$checkResult['isSuspended'])
                {
                    $canInvite = $this->service->getInviteToChatPrivacySettings(PEEP::getUser()->getId(), $userId);
                    if (!$canInvite)
                    {
                        $checkResult['isSuspended'] = true;
                        $checkResult['suspendReasonMessage'] = PEEP::getLanguage()->text('mailbox', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($userId)));
                    }
                }

                if ( $checkResult['isSuspended'] )
                {
                    $linkId = 'mb' . rand(10, 1000000);
                    $script = "\$('#" . $linkId . "').click(function(){

                window.PEEP.error(".json_encode($checkResult['suspendReasonMessage']).");

            });";

                    PEEP::getDocument()->addOnloadScript($script);
                }
                else
                {
                    $linkId = 'mb' . rand(10, 1000000);
                    $linkSelector = '#' . $linkId;
                    $data = $this->service->getUserInfo($userId);
                    $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                var userData = {$data};

                $.post(PEEPMailbox.openDialogResponderUrl, {
                    userId: userData.opponentId,
                    checkStatus: 2
                }, function(data){

                    if ( typeof data != \'undefined\'){
                        if ( typeof data[\'warning\'] != \'undefined\' && data[\'warning\'] ){
                            PEEP.message(data[\'message\'], data[\'type\']);
                            return;
                        }
                        else{
                            if (data[\'use_chat\'] && data[\'use_chat\'] == \'promoted\'){
                                PEEP.Mailbox.contactManagerView.showPromotion();
                            }
                            else{
                                PEEP.Mailbox.usersCollection.add(data);
                                PEEP.trigger(\'mailbox.open_dialog\', {convId: data[\'convId\'], opponentId: data[\'opponentId\'], mode: \'chat\'});
                            }
                        }
                    }
                }, \'json\').complete(function(){

                        $(\'#peep_chat_now_\'+userData.opponentId).removeClass(\'peep_hidden\');

                        $(\'#peep_preloader_content_\'+userData.opponentId).addClass(\'peep_hidden\');
                    });

            });', array('linkSelector'=>$linkSelector, 'data'=>$data));

                    PEEP::getDocument()->addOnloadScript($script);
                }

                $resultArray = array(
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => PEEP::getLanguage()->text('mailbox', 'send_message'),
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
                );

                $event->add($resultArray);

                return;
            }
        }

        if ( !PEEP::getUser()->isAuthorized('mailbox', 'send_message') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'send_message');
            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $linkId = 'mb' . rand(10, 1000000);
                $linkSelector = '#' . $linkId;
                $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');

                });', array('linkSelector'=>$linkSelector));

                PEEP::getDocument()->addOnloadScript($script);

                $resultArray = array(
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => PEEP::getLanguage()->text('mailbox', 'create_conversation_button'),
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
                    BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
                );

                $event->add($resultArray);
            }

            return;
        }

        $checkResult = $this->service->checkUser(PEEP::getUser()->getId(), $userId);

        if ( $checkResult['isSuspended'] )
        {
            $linkId = 'mb' . rand(10, 1000000);
            $script = "\$('#" . $linkId . "').click(function(){

                window.PEEP.error(".json_encode($checkResult['suspendReasonMessage']).");

            });";

            PEEP::getDocument()->addOnloadScript($script);
        }
        else
        {
            $linkId = 'mb' . rand(10, 1000000);
            $linkSelector = '#' . $linkId;
            $data = $this->service->getUserInfo($userId);
            $script = UTIL_JsGenerator::composeJsString('$({$linkSelector}).click(function(){

                var data = {$data};

                PEEP.trigger("mailbox.open_new_message_form", data);

            });', array('linkSelector'=>$linkSelector, 'data'=>$data));

            PEEP::getDocument()->addOnloadScript($script);
        }

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => PEEP::getLanguage()->text('mailbox', 'create_conversation_button'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message",
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
        );

        $event->add($resultArray);
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $modes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();

        if (in_array('mail', $modes))
        {
            $e->add(array(
                'section' => 'mailbox',
                'action' => 'mailbox-new_message',
                'sectionIcon' => 'peep_ic_mail',
                'sectionLabel' => PEEP::getLanguage()->text('mailbox', 'messages_email_notifications_section_label'),
                'description' => PEEP::getLanguage()->text('mailbox', 'messages_email_notifications_new_message'),
                'selected' => true
            ));
        }

        if (in_array('chat', $modes))
        {
            $e->add(array(
                'section' => 'mailbox',
                'action' => 'mailbox-new_chat_message',
                'sectionIcon' => 'peep_ic_mail',
                'sectionLabel' => PEEP::getLanguage()->text('mailbox', 'messages_email_notifications_section_label'),
                'description' => PEEP::getLanguage()->text('mailbox', 'messages_email_notifications_new_chat_message'),
                'selected' => true
            ));
        }
    }

    public function onSendMessage( PEEP_Event $e )
    {
        $params = $e->getParams();

        PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['senderId'] ));
        PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['recipientId'] ));
    }

    public function onAvatarToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'title' => PEEP::getLanguage()->text('mailbox', 'mailbox'),
            'iconClass' => 'peep_ic_mail',
            'url' => PEEP::getRouter()->urlForRoute('mailbox_default'),
            'order' => 2
        ));
    }

    public function mailboxAdsEnabled( BASE_CLASS_EventCollector $event )
    {
        $event->add('mailbox');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $mailboxEvent = new PEEP_Event('mailbox.admin.add_auth_labels');
        PEEP::getEventManager()->trigger($mailboxEvent);
        $groupName = 'mailbox';

        $data = $mailboxEvent->getData();
        if (!empty($data))
        {
            $read_message_action = BOL_AuthorizationService::getInstance()->findAction($groupName, 'read_message');
            if (!empty($read_message_action))
            {
                $authorization = PEEP::getAuthorization();

                $authorization->deleteAction($groupName, 'read_message');
                $authorization->deleteAction($groupName, 'send_message');
                $authorization->deleteAction($groupName, 'reply_to_message');
            }

            $actions = $data['actions'];
        }
        else
        {
            $modes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();

            if (in_array('mail', $modes))
            {
                $read_message_action = BOL_AuthorizationService::getInstance()->findAction($groupName, 'read_message');
                if (empty($read_message_action))
                {
                    $authorization = PEEP::getAuthorization();

                    $authorization->addAction($groupName, 'read_message');
                    $authorization->addAction($groupName, 'send_message');
                    $authorization->addAction($groupName, 'reply_to_message');
                }
            }

            $actions = array(
                'send_message' => $language->text('mailbox', 'auth_action_label_send_message'),
                'read_message' => $language->text('mailbox', 'auth_action_label_read_message'),
                'reply_to_message' => $language->text('mailbox', 'auth_action_label_reply_to_message'),

                'send_chat_message' => $language->text('mailbox', 'auth_action_label_send_chat_message'),
                'read_chat_message' => $language->text('mailbox', 'auth_action_label_read_chat_message'),
                'reply_to_chat_message' => $language->text('mailbox', 'auth_action_label_reply_to_chat_message'),
            );
        }

        $event->add(
            array(
                'mailbox' => array(
                    'label' => $language->text('mailbox', 'auth_group_label'),
                    'actions' => $actions
                )
            )
        );
    }

    public function markConversation( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = (int)$params['userId'];

        PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_NEW_CONVERSATION_COUNT . ($userId) ));
        //PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($userId) ));
    }

    public function deleteConversation( PEEP_Event $event )
    {
        $params = $event->getParams();
        $dto = $params['conversationDto'];
        /* @var $dto MAILBOX_BOL_Conversation */
        if ( $dto )
        {
            PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->initiatorId) ));
            PEEP::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->interlocutorId) ));
        }
    }

    public function consoleSendList( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];

        $conversationListByUserId = $this->service->getConversationListForConsoleNotificationMailer($userIdList);

        $conversationIdList = array();

        foreach ( $conversationListByUserId as $recipientId => $conversationList )
        {
            foreach ( $conversationList as $conversation )
            {
                $conversationIdList[$conversation['id']] = $conversation['id'];
            }
        }

        $result = $this->service->getConversationListByIdList($conversationIdList);
        $conversationList = array();

        foreach( $result as $conversation )
        {
            $conversationList[$conversation->id] = $conversation;
        }

        foreach ( $conversationListByUserId as $recipientId => $list )
        {
            foreach ( $list as $conversation )
            {
                $senderId = ($conversation['initiatorId'] == $recipientId) ? $conversation['interlocutorId'] : $conversation['initiatorId'];

                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array( $senderId ) );
                $avatar = $avatars[$senderId];

                $conversationUrl = PEEP::getRouter()->urlForRoute('mailbox_messages_default');

                if ($conversation['subject'] == MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT)
                {
                    $actionName = 'mailbox-new_chat_message';
                }
                else
                {
                    $actionName = 'mailbox-new_message';
                    $conversationUrl = $this->service->getConversationUrl($conversation['id']);
                }

                $event->add(array(
                    'pluginKey' => 'mailbox',
                    'entityType' => 'mailbox-conversation',
                    'entityId' => $conversation['id'],
                    'userId' => $recipientId,
                    'action' => $actionName,
                    'time' => $conversation['timeStamp'],

                    'data' => array(
                        'avatar' => $avatar,
                        'string' => PEEP::getLanguage()->text('mailbox', 'email_notifications_comment', array(
                                'userName' => BOL_UserService::getInstance()->getDisplayName($senderId),
                                'userUrl' => BOL_UserService::getInstance()->getUserUrl($senderId),
                                'conversationUrl' => $conversationUrl
                            )),
                       'content' => $conversation['text']
                    )
                ));

                if( !empty($conversationList[$conversation['id']]) )
                {
                    $conversationList[$conversation['id']]->notificationSent = 1;
                    $this->service->saveConversation($conversationList[$conversation['id']]);
                }
            }
        }
    }

    public function onPluginInit()
    {
        $handlerAttributes = PEEP::getRequestHandler()->getHandlerAttributes();
        $event = new PEEP_Event('plugin.mailbox.on_plugin_init.handle_controller_attributes', array('handlerAttributes'=>$handlerAttributes));
        PEEP::getEventManager()->trigger($event);

        $handleResult = $event->getData();

        if ($handleResult === false)
        {
            return;
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            return;
        }
        else
        {

            if ( !BOL_UserService::getInstance()->isApproved() )
            {
                return;
            }

            $user = PEEP::getUser()->getUserObject();

            if (BOL_UserService::getInstance()->isSuspended($user->getId()))
            {
                return;
            }

            if ( (int) $user->emailVerify === 0 && PEEP::getConfig()->getValue('base', 'confirm_email') )
            {
                return;
            }
        }

        $im_toolbar = new MAILBOX_CMP_Toolbar();
        PEEP::getDocument()->appendBody($im_toolbar->render());
    }

    public function onHandleControllerAttributes( PEEP_Event $event )
    {
        $params = $event->getParams();

        $handlerAttributes = $params['handlerAttributes'];

        if ($handlerAttributes['controller'] == 'BASE_CTRL_MediaPanel')
        {
            $event->setData(false);
        }

        if ($handlerAttributes['controller'] == 'SUPPORTTOOLS_CTRL_Client')
        {
            $event->setData(false);
        }
    }

    public function onCollectPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $activeModes = $this->service->getActiveModeList();

        if (in_array('chat', $activeModes))
        {
            $action = array(
                'key' => 'mailbox_invite_to_chat',
                'pluginKey' => 'mailbox',
                'label' => $language->text('mailbox', 'privacy_action_invite_to_chat'),
                'description' => '',
                'defaultValue' => 'everybody'
            );
            $event->add($action);
        }
    }

    public function onShowOnlineButton( PEEP_Event $event )
    {
        $params = $event->getParams();

        if (empty($params['userId']))
            return false;

        $activeModes = $this->service->getActiveModeList();

        if (!in_array('chat', $activeModes))
        {
            return false;
        }

        if ( BOL_UserService::getInstance()->isBlocked($params['userId'], $params['onlineUserId']) )
        {
            return false;
        }

        $eventParams = array(
            'action' => 'mailbox_invite_to_chat',
            'ownerId' => $params['onlineUserId'],
            'viewerId' => PEEP::getUser()->getId()
        );

        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }

        if ( !PEEP::getAuthorization()->isUserAuthorized($params['userId'], 'mailbox', 'send_chat_message') )
        {
            return false;
        }

        return true;
    }

    public function onApiPing( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            return;
        }

        $model = new MAILBOX_CLASS_Model();
        $model->updateWithData($params);

        $data = $event->getData();

        if (empty($data))
        {
            $data = array();
            $data['mailbox'] = $model->getResponse();
        }
        else if (is_array($data))
        {
            $data['mailbox'] = $model->getResponse();
        }

        $event->setData($data);
    }

    public function onPing( PEEP_Event $event )
    {
        $eventParams = $event->getParams();
        $params = $eventParams['params'];

        if ($eventParams['command'] == 'mailbox_api_ping')
        {
            return $this->onApiPing($event);
        }

        if ($eventParams['command'] != 'mailbox_ping')
        {
            return;
        }

        if ( empty($_SESSION['lastRequestTimestamp']) )
        {
            $_SESSION['lastRequestTimestamp'] = (int)$params['lastRequestTimestamp'];
        }

        if ( ((int)$params['lastRequestTimestamp'] - (int) $_SESSION['lastRequestTimestamp']) < 3 )
        {
            $event->setData(array('error'=>"Too much requests"));
        }

        $_SESSION['lastRequestTimestamp'] = (int)$params['lastRequestTimestamp'];

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $event->setData(array('error'=>"You have to sign in"));
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            $event->setData(array('error'=>"Ajax request required"));
        }

        $userId = PEEP::getUser()->getId();

        /** SET **/

        if (!empty($params['readMessageList']))
        {
            $this->service->markMessageIdListRead($params['readMessageList']);
            $this->service->resetUserLastData($userId);
        }

        if (!empty($params['viewedConversationList']))
        {
            $this->service->setConversationViewedInConsole($params['viewedConversationList'], PEEP::getUser()->getId());
            $this->service->resetUserLastData($userId);
        }

        $ajaxActionResponse = array();
        if (!empty($params['ajaxActionData']))
        {
            $this->service->resetUserLastData($userId);

            foreach($params['ajaxActionData'] as $action)
            {
                switch($action['name'])
                {
                    case 'postMessage':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->postMessage($action['data']);

                        if (!empty($ajaxActionResponse[$action['uniqueId']]['message']))
                        {
                            $params['lastMessageTimestamp'] = $ajaxActionResponse[$action['uniqueId']]['message']['timeStamp'];
                        }
                        break;
                    case 'getLog':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->getLog($action['data']);
                        break;
                    case 'markConversationUnRead':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->markConversationUnRead($action['data']);
                        break;
                    case 'markConversationRead':
                        $this->ajaxService->markConversationRead($action['data']);
                        break;
                    case 'loadMoreConversations':

                        if (isset($action['data']['searching']) && $action['data']['searching'] == 1)
                        {
                            $conversationIds = MAILBOX_BOL_ConversationDao::getInstance()->findConversationByKeyword($action['data']['kw'], 8, $action['data']['from']);
                            $ajaxActionResponse[$action['uniqueId']] = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdList( $conversationIds );
                        }
                        else
                        {
                            $ajaxActionResponse[$action['uniqueId']] = $this->service->getConversationListByUserId( PEEP::getUser()->getId(), $action['data']['from'], 10 );
                        }
                        break;
                    case 'bulkActions':
                        $ajaxActionResponse[$action['uniqueId']] = $this->ajaxService->bulkActions($action['data']);
                        break;
                }
            }
        }
        /** **/

        /** GET **/
        $response = $this->service->getLastData($params);
        if (!empty($ajaxActionResponse))
        {
            $response['ajaxActionResponse'] = $ajaxActionResponse;
        }

        $markedUnreadConversationList = $this->service->getMarkedUnreadConversationList( PEEP::getUser()->getId() );
        if (count($markedUnreadConversationList) > 0)
        {
            $response['markedUnreadConversationList'] = $markedUnreadConversationList;
        }

        /** **/

        $event->setData($response);
    }

    public function onAcceptWink( PEEP_Event $event )
    {
        $params = $event->getParams();

        $activeModeList = $this->service->getActiveModeList();
        $mode = (in_array('chat', $activeModeList)) ? 'chat' : 'mail';

        $content = json_encode($params['content']);

        if ($mode == 'chat')
        {
            $conversationId = $this->service->getChatConversationIdWithUserById($params['userId'], $params['partnerId']);
            if (empty($conversationId))
            {
                $conversation = $this->service->createChatConversation($params['userId'], $params['partnerId']);
            }
            else
            {
                $conversation = $this->service->getConversation($conversationId);
            }
        }
        else
        {
            $conversationId = $this->service->getWinkConversationIdWithUserById($params['userId'], $params['partnerId']);
            if (empty($conversationId))
            {
                $conversation = $this->service->createConversation($params['userId'], $params['partnerId'], MAILBOX_BOL_ConversationDao::WINK_CONVERSATION_SUBJECT);
            }
            else
            {
                $conversation = $this->service->getConversation($conversationId);
            }
        }

        if (!empty($conversation))
        {
            $message = $this->service->createMessage($conversation, $params['userId'], $content);
            $this->service->markMessageAsSystem($message->id);
            $this->service->markMessageAuthorizedToRead($message->id);

            $data = array(
                'conversationId' => $conversation->id,
                'mode' => $mode
            );

            $event->setData($data);
        }
    }

    public function onWinkBack( PEEP_Event $event )
    {
        $params = $event->getParams();

        $message = $this->service->getMessage($params['content']['params']['messageId']);
        $messageContent = json_decode($message->text, true);
        $messageContent['params']['winkBackEnabled'] = 0;
        $message->text = json_encode($messageContent);
        $this->service->saveMessage($message);

        $content = json_encode($params['content']);

        $conversation = $this->service->getConversation($params['conversationId']);
        $message = $this->service->createMessage($conversation, $params['partnerId'], $content);
        $this->service->markMessageAsSystem($message->id);
        $this->service->markMessageAuthorizedToRead($message->id);
    }

    public function onAttachmentUpload( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ($params['pluginKey'] != 'mailbox')
        {
            return;
        }

        //mailbox_dialog_{convId}_{opponentId}_{hash}
        $uidParams = explode('_', $params['uid']);

        if (count($uidParams) != 5)
        {
            return;
        }

        if ($uidParams[0] != 'mailbox')
        {
            return;
        }

        if ($uidParams[1] != 'dialog')
        {
            return;
        }

        $conversationId = $uidParams[2];
        $userId = PEEP::getUser()->getId();
//        $opponentId = $uidParams[3];

        $files = $params['files'];
        if (!empty($files))
        {
            $conversation = $this->service->getConversation($conversationId);
            try
            {
                $message = $this->service->createMessage($conversation, $userId, PEEP::getLanguage()->text('mailbox', 'attachment'));
                $this->service->addMessageAttachments($message->id, $files);
            }
            catch(InvalidArgumentException $e)
            {

            }
        }
    }

    public function onRenderOembed( PEEP_Event $event )
    {
        $params = $event->getParams();

        if (isset($params['getPreview']) && $params['getPreview'])
        {
            $content = $params['href'];
        }
        else
        {
            $tempCmp = new MAILBOX_CMP_OembedAttachment($params['message'], $params);
            $content = $tempCmp->render();
        }
        $event->setData($content);
    }

    public function onCollectConsoleItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if (PEEP::getUser()->isAuthenticated())
        {
            $item = new MAILBOX_CMP_ConsoleMailbox();
            $event->addItem($item, 4);
        }
    }

    public function onLoadConsoleList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $userId = PEEP::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

        $conversations = $this->service->getConsoleConversationList($userId, 0, 8, $params['console']['time'], $params['ids']);

        $conversationIdList = array();
        foreach ( $conversations as $conversationData )
        {
            if (!in_array($conversationData['conversationId'], $conversationIdList))
            {
                $conversationIdList[] = $conversationData['conversationId'];
            }

            $mode = $this->service->getConversationMode($conversationData['conversationId']);
            $conversationItem = $this->service->getConversationItem($mode, $conversationData['conversationId']);
            $item = new MAILBOX_CMP_ConsoleMessageItem($conversationItem);

            $event->addItem($item->render(), $conversationData['conversationId']);
        }

        $this->service->setConversationViewedInConsole($conversationIdList, $userId);
    }

    /**
     * Application event methods
     */
    public function getUnreadMessageCount( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $ignoreList = !empty($params['ignoreList']) ? (array)$params['ignoreList'] : array();
        $time = !empty($params['time']) ? (int)$params['time'] : time();

        $data = $this->service->getUnreadMessageCount($userId, $ignoreList, $time);

        $event->setData( $data );

        return $data;
    }

    public function getChatUserList( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        $from = 0;
        $count = 10;

        if (isset($params['from']))
        {
            $from = (int)$params['from'];
        }

        if (isset($params['count']))
        {
            $count = (int)$params['count'];
        }

        $list = $this->service->getChatUserList($userId, $from, $count);
        $event->setData( $list );

        return $list;
    }

    public function postMessage( PEEP_Event $event )
    {
       $params = $event->getParams();

        if (empty($params['mode']) && empty($params['conversationId']))
        {
            $data = array('error'=>true, 'message'=>'Undefined conversation');
            $event->setData($data);
            return $data;
        }

        $checkResult = $this->service->checkUser($params['userId'], $params['opponentId']);

        if ($checkResult['isSuspended'])
        {
            $data = array('error'=>true, 'message'=>$checkResult['suspendReasonMessage'], 'suspendReason'=>$checkResult['suspendReason']);

            $event->setData($data);
            return $data;
        }

            $conversationId = $this->service->getChatConversationIdWithUserById($params['userId'], $params['opponentId']);

            if (empty($conversationId))
            {
            $actionName = 'send_chat_message';
        }
        else
        {
            $firstMessage = $this->service->getFirstMessage($conversationId);

            if (empty($firstMessage))
            {
                $actionName = 'send_chat_message';
            }
            else
            {
                $actionName = 'reply_to_chat_message';
            }
        }

        $isAuthorized = PEEP::getUser()->isAuthorized('mailbox', $actionName);
        if ( !$isAuthorized )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
            if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
            {
                $data = array('error' => true, 'message'=>strip_tags($status['msg']), "promoted" => true);
            }
            else
            {
                if ($status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE)
                {
                    $language = PEEP::getLanguage();
                    $data = array('error' => true, 'message'=>$language->text('mailbox', $actionName.'_permission_denied'), "promoted" => false);
                }
            }
            $event->setData($data);
            return $data;
        }

        if (!empty($params['mode']) && $params['mode'] == 'chat')
        {
            if (empty($conversationId))
            {
                $conversation = $this->service->createChatConversation($params['userId'], $params['opponentId']);
                $conversationId = $conversation->getId();
            }

            $conversation = $this->service->getConversation($conversationId);

            $message = $this->service->createMessage($conversation, $params['userId'], $params['text']);

            if ( isset($params['isSystem']) && $params['isSystem'] )
            {
                $this->service->markMessageAsSystem($message->id);
            }

            $this->service->markUnread(array($conversationId), $params['opponentId']);

            $messageData = $this->service->getMessageDataForApi($message);

            $data = array('error'=>false, 'message'=>$messageData);

            $event->setData($data);

            BOL_AuthorizationService::getInstance()->trackAction('mailbox', $actionName);

            return $data;
        }
    }

    public function postReplyMessage( PEEP_Event $event )
    {
       $params = $event->getParams();

        if (empty($params['mode']) && empty($params['conversationId']))
        {
            $data = array('error'=>true, 'message'=>'Undefined conversation');
            $event->setData($data);
            return $data;
        }

        $checkResult = $this->service->checkUser($params['userId'], $params['opponentId']);

        if ($checkResult['isSuspended'])
        {
            $data = array('error'=>true, 'message'=>$checkResult['suspendReasonMessage'], 'suspendReason'=>$checkResult['suspendReason']);

            $event->setData($data);
            return $data;
        }

        $conversationId = $params['conversationId'];
        $actionName = 'reply_to_message';

        $isAuthorized = PEEP::getUser()->isAuthorized('mailbox', $actionName);
        if ( !$isAuthorized )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
            if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
            {
                $data = array('error' => true, 'message'=>strip_tags($status['msg']), "promoted" => true);
            }
            else
            {
                if ($status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE)
                {
                    $language = PEEP::getLanguage();
                    $data = array('error' => true, 'message'=>$language->text('mailbox', $actionName.'_permission_denied'), "promoted" => false);
                }
            }
            $event->setData($data);
            return $data;
        }

        if (!empty($params['mode']) && $params['mode'] == 'mail')
        {
            $conversation = $this->service->getConversation($conversationId);

            $message = $this->service->createMessage($conversation, $params['userId'], $params['text']);

            if ( isset($params['isSystem']) && $params['isSystem'] )
            {
                $this->service->markMessageAsSystem($message->id);
            }

            $this->service->markUnread(array($conversationId), $params['opponentId']);

            $messageData = $this->service->getMessageDataForApi($message);

            $data = array('error'=>false, 'message'=>$messageData);

            $event->setData($data);

            BOL_AuthorizationService::getInstance()->trackAction('mailbox', $actionName);

            return $data;
        }
    }

    public function getNewMessages( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $lastMessageTimestamp = $params['lastMessageTimestamp'];

        $data = $this->service->getChatNewMessages($userId, $opponentId, $lastMessageTimestamp);

        $event->setData($data);

        return $data;
    }

    public function getNewMessagesForConversation( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['conversationId']) )
        {
            $event->setData(array());
            
            return array();
        }

        $conversationId = (int)$params['conversationId'];
        $lastMessageTimestamp = !empty($params['lastMessageTimestamp']) ? (int)$params['lastMessageTimestamp'] : null;
        $messages = $this->service->getNewMessagesForConversation($conversationId, $lastMessageTimestamp);
        $event->setData($messages);

        return $messages;
    }

    public function getMessages( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        
        if ( empty($params['conversationId']) ) // Backward compatibility
        {
            if ( !empty($params['opponentId']) )
            {
                $conversationId = $this->service->getChatConversationIdWithUserById($userId, $params['opponentId']);
            }
        }
        else
        {
            $conversationId = $params['conversationId'];
        }
        
        $data = $this->service->getMessagesForApi($userId, $conversationId);

        $event->setData($data);

        return $data;
    }

    public function getHistory( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $beforeMessageId = $params['beforeMessageId'];

        $data = array();

        $conversationId = $this->service->getChatConversationIdWithUserById($userId, $opponentId);
        if ($conversationId)
        {
            $data = $this->service->getConversationHistoryForApi($conversationId, $beforeMessageId);
        }

        $event->setData($data);

        return $data;
    }
    /**
     *
     */

    public function showSendMessageButton( PEEP_Event $event )
    {
        $event->setData(true);
    }

    public function onFriendRequestAccepted(PEEP_Event $event)
    {
        $params = $event->getParams();

        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['senderId']);
        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['recipientId']);
    }

    public function resetAllUsersLastData(PEEP_Event $event)
    {
        $params = $event->getParams();

        MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();
    }

    public function onUserUnregister(PEEP_Event $event)
    {
        $params = $event->getParams();

        MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();

        $userId = (int) $params['userId'];

        $messageList = MAILBOX_BOL_MessageDao::getInstance()->findUserSentUnreadMessages($userId);
        $messageIdList = array();
        /**
         * @var MAILBOX_BOL_Message $message
         */
        foreach($messageList as $message)
        {
            MAILBOX_BOL_ConversationService::getInstance()->markMessageIdListReadByUser(array($message->id), $message->recipientId);
        }
    }
    
    public function onChangeUserAvatar(PEEP_Event $event)
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($params['userId']);
        }
    }

    public function onMarkAsRead( PEEP_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->markRead(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function onMarkUnread( PEEP_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->markUnread(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function getConversationId( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['opponentId']) )
        {
            $event->setData(null);

            return null;
        }

        $userId = (int)$params['userId'];
        $opponentId = (int)$params['opponentId'];

        $conversationId = $this->service->getChatConversationIdWithUserById($userId, $opponentId);
        $event->setData($conversationId);

        return $conversationId;
    }
    
    public function onDeleteConversation( PEEP_Event $event )
    {
        $params = $event->getParams();

        $count = $this->service->deleteConversation(is_array($params['conversationId']) ? $params['conversationId'] : array($params['conversationId']), $params['userId']);

        $event->setData($count);

        return $count;
    }

    public function onCreateConversation( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $opponentId = $params['opponentId'];
        $text = $params['text'];
        $subject = $params['subject'];

        $userSendMessageIntervalOk = $this->service->checkUserSendMessageInterval($userId);

        if ( !$userSendMessageIntervalOk )
        {
            $send_message_interval = (int)PEEP::getConfig()->getValue('mailbox', 'send_message_interval');
            throw new InvalidArgumentException(PEEP::getLanguage()->text('mailbox', 'feedback_send_message_interval_exceed', array('send_message_interval'=>$send_message_interval)));
        }

        $conversation = $this->service->createConversation($userId, $opponentId, $subject, $text);

        $event->setData($conversation);

        return $conversation;
    }

    public function onGetActiveModeList( PEEP_Event $event )
    {
        $activeModeList = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $event->setData($activeModeList);

        return $activeModeList;
    }

    public function onAuthorizeAction( PEEP_Event $event )
    {
        $params = $event->getParams();
        $result = $this->ajaxService->authorizeActionForApi( $params );
        $event->setData($result);
        return $result;
    }

    public function onFindUser( PEEP_Event $event )
    {
        $result = array();
        $params = $event->getParams();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $event->setData($result);
            return $result;
        }

        $kw = empty($params['term']) ? null : $params['term'];
        $idList = empty($params['idList']) ? null : $params['idList'];

        $context = empty($params["context"]) ? 'api' : $params["context"];
        $userId = PEEP::getUser()->getId();

        $result = $this->ajaxService->getSuggestEntries($userId, $kw, $idList, $context);

        $event->setData($result);
        return $result;
    }
}