<?php

class MAILBOX_CMP_Toolbar extends PEEP_Component
{
    private $useChat;

    public function __construct()
    {
        parent::__construct();


        $handlerAttributes = PEEP::getRequestHandler()->getHandlerAttributes();
        $event = new PEEP_Event('plugin.mailbox.on_plugin_init.handle_controller_attributes', array('handlerAttributes'=>$handlerAttributes));
        PEEP::getEventManager()->trigger($event);

        $handleResult = $event->getData();

        if ($handleResult === false)
        {
            $this->setVisible(false);
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
        else
        {
            if ( !BOL_UserService::getInstance()->isApproved() && PEEP::getConfig()->getValue('base', 'mandatory_user_approve') )
            {
                $this->setVisible(false);
            }

            $user = PEEP::getUser()->getUserObject();

            if (BOL_UserService::getInstance()->isSuspended($user->getId()))
            {
                $this->setVisible(false);
            }

            if ( (int) $user->emailVerify === 0 && PEEP::getConfig()->getValue('base', 'confirm_email') )
            {
                $this->setVisible(false);
            }

            $this->useChat = BOL_AuthorizationService::STATUS_AVAILABLE;

            $this->assign('useChat', $this->useChat);
            $this->assign('msg', '');
        }
    }

    public function render()
    {
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin("base")->getStaticJsUrl() . "jquery-ui.min.js");
        PEEP::getDocument()->addScript( PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl().'underscore-min.js', 'text/javascript', 3000 );
        PEEP::getDocument()->addScript( PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl().'backbone-min.js', 'text/javascript', 3000 );

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'audio-player.js');
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'mailbox.js', 'text/javascript', 3000);
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('mailbox')->getStaticJsUrl() . 'contactmanager.js', 'text/javascript', 3001);

        PEEP::getDocument()->addStyleSheet( PEEP::getPluginManager()->getPlugin('mailbox')->getStaticCssUrl().'mailbox.css' );

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $userId = PEEP::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
        if ( empty($avatarUrl) )
        {
            $avatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }
        $profileUrl = BOL_UserService::getInstance()->getUserUrl($userId);

        $jsGenerator = UTIL_JsGenerator::newInstance();
        $jsGenerator->setVariable('PEEPMailbox.documentTitle', PEEP::getDocument()->getTitle());
        $jsGenerator->setVariable('PEEPMailbox.soundEnabled', (bool) BOL_PreferenceService::getInstance()->getPreferenceValue('mailbox_user_settings_enable_sound', $userId));
        $jsGenerator->setVariable('PEEPMailbox.showOnlineOnly', (bool) BOL_PreferenceService::getInstance()->getPreferenceValue('mailbox_user_settings_show_online_only', $userId));
        $jsGenerator->setVariable('PEEPMailbox.showAllMembersMode', (bool)PEEP::getConfig()->getValue('mailbox', 'show_all_members') );
        $jsGenerator->setVariable('PEEPMailbox.soundSwfUrl', PEEP::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'js/player.swf');
        $jsGenerator->setVariable('PEEPMailbox.soundUrl', PEEP::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'sound/receive.mp3');
        $jsGenerator->setVariable('PEEPMailbox.defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $jsGenerator->setVariable('PEEPMailbox.serverTimezoneOffset', date('Z') / 3600);
        $jsGenerator->setVariable('PEEPMailbox.useMilitaryTime', (bool) PEEP::getConfig()->getValue('base', 'military_time'));
        $jsGenerator->setVariable('PEEPMailbox.getHistoryResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'getHistory'));
        $jsGenerator->setVariable('PEEPMailbox.openDialogResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'updateUserInfo'));
        $jsGenerator->setVariable('PEEPMailbox.attachmentsSubmitUrl', PEEP::getRouter()->urlFor('BASE_CTRL_Attachment', 'addFile'));
        $jsGenerator->setVariable('PEEPMailbox.attachmentsDeleteUrl',  PEEP::getRouter()->urlFor('BASE_CTRL_Attachment', 'deleteFile'));
        $jsGenerator->setVariable('PEEPMailbox.authorizationResponderUrl',  PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'authorization'));
        $jsGenerator->setVariable('PEEPMailbox.responderUrl', PEEP::getRouter()->urlFor("MAILBOX_CTRL_Mailbox", "responder"));
        $jsGenerator->setVariable('PEEPMailbox.userListUrl', PEEP::getRouter()->urlForRoute('mailbox_user_list'));
        $jsGenerator->setVariable('PEEPMailbox.convListUrl', PEEP::getRouter()->urlForRoute('mailbox_conv_list'));
        $jsGenerator->setVariable('PEEPMailbox.pingResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'ping'));
        $jsGenerator->setVariable('PEEPMailbox.settingsResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'settings'));
        $jsGenerator->setVariable('PEEPMailbox.userSearchResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'rsp'));
        $jsGenerator->setVariable('PEEPMailbox.bulkOptionsResponderUrl', PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'bulkOptions'));

        $plugin_update_timestamp = 0;
        if ( PEEP::getConfig()->configExists('mailbox', 'plugin_update_timestamp') )
        {
            $plugin_update_timestamp = PEEP::getConfig()->getValue('mailbox', 'plugin_update_timestamp');
        }
        $jsGenerator->setVariable('PEEPMailbox.pluginUpdateTimestamp', $plugin_update_timestamp);

        $todayDate = date('Y-m-d', time());
        $jsGenerator->setVariable('PEEPMailbox.todayDate', $todayDate);
        $todayDateLabel = UTIL_DateTime::formatDate(time(), true);
        $jsGenerator->setVariable('PEEPMailbox.todayDateLabel', $todayDateLabel);

        $activeModeList = $conversationService->getActiveModeList();
        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        $this->assign('chatModeEnabled', $chatModeEnabled);
        $jsGenerator->setVariable('PEEPMailbox.chatModeEnabled', $chatModeEnabled);
        $jsGenerator->setVariable('PEEPMailbox.useChat', $this->useChat);

        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $this->assign('mailModeEnabled', $mailModeEnabled);
        $jsGenerator->setVariable('PEEPMailbox.mailModeEnabled', $mailModeEnabled);

        $isAuthorizedSendMessage = PEEP::getUser()->isAuthorized('mailbox', 'send_message');
        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);

        $configs = PEEP::getConfig()->getValues('mailbox');
//        if ( !empty($configs['enable_attachments']))
//        {
            PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
//        }

        $this->assign('im_sound_url', PEEP::getPluginManager()->getPlugin('mailbox')->getStaticUrl() . 'sound/receive.mp3');


        /* DEBUG MODE */
        $debugMode = false;
        $jsGenerator->setVariable('im_debug_mode', $debugMode);
        $this->assign('debug_mode', $debugMode);

        $variables = $jsGenerator->generateJs();

        $details = array(
            'userId' => $userId,
            'displayName' => $displayName,
            'profileUrl' => $profileUrl,
            'avatarUrl' => $avatarUrl
        );
        PEEP::getDocument()->addScriptDeclaration("PEEPMailbox.userDetails = " . json_encode($details) . ";\n " . $variables);

        PEEP::getLanguage()->addKeyForJs('mailbox', 'find_contact');
        PEEP::getLanguage()->addKeyForJs('base', 'user_block_message');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'send_message_failed');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'confirm_conversation_delete');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'silent_mode_off');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'silent_mode_on');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'show_all_users');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'show_all_users');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'show_online_only');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'new_message');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'mail_subject_prefix');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'chat_subject_prefix');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'new_message_count');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'chat_message_empty');
        PEEP::getLanguage()->addKeyForJs('mailbox', 'text_message_invitation');

        $avatar_proto_data = array('url' => 1, 'src' => BOL_AvatarService::getInstance()->getDefaultAvatarUrl(), 'class' => 'talk_box_avatar');
        $this->assign('avatar_proto_data', $avatar_proto_data);

        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $this->assign('online_list_url', PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')));

        /**/

        $actionPromotedText = '';

        $isAuthorizedReplyToMessage = PEEP::getUser()->isAuthorized('mailbox', 'reply_to_chat_message');
        $isAuthorizedSendMessage = PEEP::getUser()->isAuthorized('mailbox', 'send_chat_message');
        $isAuthorized = $isAuthorizedReplyToMessage || $isAuthorizedSendMessage;
        if (!$isAuthorized)
        {
            $actionName = 'send_chat_message';
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $actionPromotedText = $status['msg'];
            }
        }
        $this->assign('replyToMessageActionPromotedText', $actionPromotedText);
        $this->assign('isAuthorizedReplyToMessage', $isAuthorized);
  

        /**/

        $lastSentMessage = $conversationService->getLastSentMessage($userId);
        $lastMessageTimestamp = (int)($lastSentMessage ? $lastSentMessage->timeStamp : 0);

        if ($chatModeEnabled)
        {
            $countOnline = BOL_UserService::getInstance()->countOnline();
            if ($countOnline < 5)
            {
                $pingInterval = 5000;
            }
            else
            {
                if ($countOnline > 15)
                {
                    $pingInterval = 15000;
                }
                else
                {
                    $pingInterval = 5000; //TODO think about ping interval here
                }
            }
        }
        else
        {
            $pingInterval = 30000;
        }

        $applicationParams = array(

            'pingInterval'=>$pingInterval,
            'lastMessageTimestamp' => $lastMessageTimestamp
        );

        $js = UTIL_JsGenerator::composeJsString('PEEP.Mailbox = new PEEPMailbox.Application({$params});', array('params'=>$applicationParams));
        PEEP::getDocument()->addOnloadScript($js, 3003);


        $js = "
        PEEP.Mailbox.contactManager = new MAILBOX_ContactManager;
        PEEP.Mailbox.contactManagerView = new MAILBOX_ContactManagerView({model: PEEP.Mailbox.contactManager});";

        PEEP::getDocument()->addOnloadScript($js, 3009);

        return parent::render();
    }
}