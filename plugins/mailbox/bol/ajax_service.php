<?php

class MAILBOX_BOL_AjaxService {

    const MAX_MESSAGE_TEXT_LENGTH = 24000;
    /**
     * Class instance
     *
     * @var MAILBOX_BOL_AjaxService
     */
    private static $classInstance;

    /**
     * @var MAILBOX_BOL_ConversationDao
     */
    private $conversationDao;

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_AjaxService
     */
    public static function getInstance() {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Class constructor
     *
     */
    protected function __construct() {
        $this->conversationDao = MAILBOX_BOL_ConversationDao::getInstance();
    }

    public function postMessage($params)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $language = PEEP::getLanguage();

        if ($errorMessage = $conversationService->checkPermissions())
        {
            return array('error'=>$errorMessage);
        }

        $userId = PEEP::getUser()->getId();

//        $userSendMessageIntervalOk = $conversationService->checkUserSendMessageInterval($userId);
//        if (!$userSendMessageIntervalOk)
//        {
//            $send_message_interval = (int)PEEP::getConfig()->getValue('mailbox', 'send_message_interval');
//            return array('error'=>$language->text('mailbox', 'feedback_send_message_interval_exceed', array('send_message_interval'=>$send_message_interval)));
//        }

        $conversationId = $params['convId'];
        if ( !isset($conversationId) )
        {
            return array('error'=>"Conversation is not defined");
        }

        $validator = new WyswygRequiredValidator();

        if ( !$validator->isValid($params['text']) )
        {
            return array('error'=>$language->text('mailbox', 'chat_message_empty'));
        }

        if (mb_strlen($params['text']) > self::MAX_MESSAGE_TEXT_LENGTH)
        {
            return array( 'error'=>$language->text('mailbox', 'message_too_long_error', array('maxLength' => self::MAX_MESSAGE_TEXT_LENGTH)) );
        }

        $conversation = $conversationService->getConversation($conversationId);
        if (empty($conversation))
        {
            $uidParams = explode('_', $params['uid']);

            if (count($uidParams) == 5 && $uidParams[0] == 'mailbox' && $uidParams[1] == 'dialog') {
                $opponentId = (int)$uidParams[3];

                $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);
                if ($conversationId != 0)
                {
                    $conversation = $conversationService->getConversation($conversationId);
                }
            }
        }

        if (empty($conversation))
        {
            $conversation = $conversationService->createChatConversation($userId, $opponentId);
            $conversationId = $conversation->getId();
        }

        $opponentId = $conversation->initiatorId == $userId ? $conversation->interlocutorId : $conversation->initiatorId;

        $checkResult = $conversationService->checkUser($userId, $opponentId);

        MAILBOX_BOL_ConversationService::getInstance()->resetUserLastData($opponentId);

        if ( $checkResult['isSuspended'] )
        {
            return array('error'=>$checkResult['suspendReasonMessage']);
        }
        $mode = $conversationService->getConversationMode($conversationId);

        $actionName = '';

        switch($mode)
        {
            case 'chat':

                $firstMessage = $conversationService->getFirstMessage($conversationId);

                if (empty($firstMessage))
                {
                    $actionName = 'send_chat_message';
                }
                else
                {
                    $actionName = 'reply_to_chat_message';
                }

                $isAuthorized = PEEP::getUser()->isAuthorized('mailbox', $actionName);
                if ( !$isAuthorized )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
                    if ( $status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE )
                    {
//                        return array('error'=>$language->text('mailbox', $actionName.'_permission_denied'));
                        return array('error'=>$status['msg']);
                    }
                }
                $params['text'] = UTIL_HtmlTag::stripTags(UTIL_HtmlTag::stripJs($params['text']));
                $params['text'] = nl2br($params['text']);

                break;

            case 'mail':
                $actionName = 'reply_to_message';
                $isAuthorized = PEEP::getUser()->isAuthorized('mailbox', $actionName);
                if ( !$isAuthorized )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
                    if ( $status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE )
                    {
//                        return array('error'=>$language->text('mailbox', $actionName.'_permission_denied'));
                        return array('error'=>$status['msg']);
                    }
                }
                $params['text'] = UTIL_HtmlTag::stripJs($params['text']);
                break;
        }


        $event = new PEEP_Event('mailbox.before_send_message', array(
            'senderId' => $userId,
            'recipientId' => $opponentId,
            'conversationId' => $conversation->id,
            'message' => $params['text']
        ), array('result' => true, 'error' => '', 'message' => $params['text'] ));
        PEEP::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !$data['result'] )
        {
            return $data;
        }

        $text = $data['message'];

        try
        {
            $message = $conversationService->createMessage($conversation, $userId, $text);

            $files = BOL_AttachmentService::getInstance()->getFilesByBundleName('mailbox', $params['uid']);

            if (!empty($files))
            {
                $conversationService->addMessageAttachments($message->id, $files);
            }

            if (!empty($params['embedAttachments']))
            {
                $oembedParams = json_decode($params['embedAttachments'], true);
                $oembedParams['message'] = $text;
                $messageParams = array(
                    'entityType'=>'mailbox',
                    'eventName'=>'renderOembed',
                    'params'=>$oembedParams
                );
                $message->isSystem = true;
                $message->text = json_encode($messageParams);

                $conversationService->saveMessage($message);
            }
        }
        catch(InvalidArgumentException $e)
        {
            return array('error'=>$e->getMessage());
        }

        if (!empty($actionName))
        {
            BOL_AuthorizationService::getInstance()->trackAction('mailbox', $actionName);
        }

        $item = $conversationService->getMessageData($message);

        return array('message'=>$item);
    }

    public function getLog($params)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        if ($errorMessage = $conversationService->checkPermissions())
        {
            return array('error'=>$errorMessage);
        }

        if (!PEEP::getUser()->isAuthenticated())
        {
            return array();
        }

        $userId = PEEP::getUser()->getId();
        $conversationId = (int)$params['convId'];

        $opponentId = (int)$params['opponentId'];
        if (!empty($opponentId))
        {
            if ( empty($conversationId) )
            {
                $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);
                if ( empty($conversationId) )
                {
                    $conversation = $conversationService->createChatConversation($userId, $opponentId);
                    $conversationId = $conversation->getId();

                    $conversation->read = 3;
                    $conversationService->saveConversation($conversation);
                }
            }
        }

        if (!empty($params['markRead']))
        {
            $conversationService->markRead(array($conversationId), $userId);
            $conversationService->setConversationViewedInConsole(array($conversationId), $userId);
            $conversationService->resetUserLastData($userId);
            $conversationService->resetUserLastData($opponentId);
        }

        return $conversationService->getConversationDataAndLog($conversationId);
    }

    /**
     * Marks conversation as UnRead
     *
     * @param array $params
     * @return boolean
     */
    public function markConversationUnRead( $params )
    {
        $userId = PEEP::getUser()->getId();
        $language = PEEP::getLanguage();

        if ( !PEEP::getUser()->isAuthenticated() || $userId === null )
        {
            return array('error'=>'User is not authenticated');
        }

        if ( empty($params['conversationId']) )
        {
            return array('error'=>'Mark conversation as unread fail! \nEmpty param conversationId!');
        }

        $conversation = (int) $params['conversationId'];
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        try
        {
            $conversationService->markUnRead(array($conversation), $userId);
        }
        catch ( Exception $e )
        {
            return array('error' => $language->text('mailbox', 'mark_unread_fail_message'));
        }

        return array( 'result'=>true, 'notice'=>$language->text('mailbox', 'mark_conversation_unread_message'));
    }

    /**
     * Marks conversation as Read
     *
     * @param array $params
     * @return boolean
     */
    public function markConversationRead( $params )
    {
        $userId = PEEP::getUser()->getId();
        $language = PEEP::getLanguage();

        if ( !PEEP::getUser()->isAuthenticated() || $userId === null )
        {
            return array('error'=>'User is not authenticated');
        }

        if ( empty($params['conversationId']) )
        {
            return array('error'=>'Mark conversation as read fail! \nEmpty param conversationId!');
        }

        $conversationId = (int) $params['conversationId'];
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        try
        {
            $conversationService->markRead(array($conversationId), $userId);
        }
        catch ( Exception $e )
        {
            return array('error' => $language->text('mailbox', 'mark_read_fail_message'));
        }

        return array( 'result'=>true );
    }

    public function authorizeAction($params)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $data = explode('_', $params['actionParams']);
        $reason = $data[0];
        $messageId = $data[1];

        $message = $conversationService->getMessage($messageId);

        if (empty($message))
        {
            return array('error'=>'Message not found');
        }

        if (!$message->wasAuthorized)
        {
            $mode = $conversationService->getConversationMode($message->conversationId);
            if ($mode == 'mail')
            {
                $actionName = 'read_message';
            }

            if ($mode == 'chat')
            {
                $actionName = 'read_chat_message';
            }

            $trackResult = BOL_AuthorizationService::getInstance()->
                    trackAction('mailbox', $actionName, array('checkInterval' => false));

            if ($trackResult['status'])
            {
                $message = $conversationService->markMessageAuthorizedToRead($messageId);
                $messageData = $conversationService->getMessageData($message);
                $messageData['authorizationActionText'] = $trackResult['msg'];

                return $messageData;
            }
            else
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
                if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                {
                    return array('error'=>$status['msg']);
                }
            }
        }
        else
        {
            $messageData = $conversationService->getMessageData($message);
            return $messageData;
        }

        return array('error'=>PEEP::getLanguage()->text('mailbox', 'message_was_not_authorized'));
    }


    public function authorizeActionForApi($params)
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $data = explode('_', $params['actionParams']);
        $reason = $data[0];
        $messageId = $data[1];

        $message = $conversationService->getMessage($messageId);

        if (empty($message))
        {
            return array('error'=>'Message not found');
        }

        if (!$message->wasAuthorized)
        {
            $mode = $conversationService->getConversationMode($message->conversationId);
            if ($mode == 'mail')
            {
                $actionName = 'read_message';
            }

            if ($mode == 'chat')
            {
                $actionName = 'read_chat_message';
            }

            $trackResult = BOL_AuthorizationService::getInstance()->trackAction('mailbox', $actionName);
            if ($trackResult['status'])
            {
                $message = $conversationService->markMessageAuthorizedToRead($messageId);
                $messageData = $conversationService->getMessageDataForApi($message);
                $messageData['authorizationActionText'] = $trackResult['msg'];

                return $messageData;
            }
            else
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
                if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                {
                    return array('error'=>$status['msg']);
                }
            }
        }
        else
        {
            $messageData = $conversationService->getMessageData($message);
            return $messageData;
        }

        return array('error'=>PEEP::getLanguage()->text('mailbox', 'message_was_not_authorized'));
    }

    public function onSearch( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $kw = $params["kw"];
        $userId = $params["userId"];
        $recipients = $params["recipients"];

        $userIds = array();

        if ( $params["preload"] )
        {
            if ( $kw === null )
            {
                $users = BOL_UserService::getInstance()->findList(0, 200);

                foreach ( $users as $u )
                {
                    $userIds[] = $u->id;
                }
            }
            else
            {
                $userIds = $this->findUsers($kw, 16);
            }
        }

        if ( !empty($recipients) )
        {
            foreach ( $recipients as $r )
            {
                list($prefix, $id) = explode("_", $r);

                if ( $prefix == 'user' )
                {
                    $userIds[] = $id;
                }
            }
        }

        $data = $this->buildData(array_unique($userIds), PEEP::getLanguage()->text('mailbox', 'selector_group_other'), array($userId));

        foreach ( $data as $item )
        {
            $event->add($item);
        }
    }

    public function onSearchForApi( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $kw = $params["kw"];
        $userId = $params["userId"];
        $recipients = $params["recipients"];

        $userIds = array();

        if ( $params["preload"] )
        {
            if ( $kw === null )
            {
                $users = BOL_UserService::getInstance()->findList(0, 200);

                foreach ( $users as $u )
                {
                    $userIds[] = $u->id;
                }
            }
            else
            {
                $userIds = $this->findUsers($kw, 16);
            }
        }

        if ( !empty($recipients) )
        {
            foreach ( $recipients as $r )
            {
                list($prefix, $id) = explode("_", $r);

                if ( $prefix == 'user' )
                {
                    $userIds[] = $id;
                }
            }
        }

        $data = $this->buildDataForApi(array_unique($userIds), PEEP::getLanguage()->text('mailbox', 'selector_group_other'), array($userId));

        foreach ( $data as $item )
        {
            $event->add($item);
        }
    }

    public function onConversationSearch( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        $kw = $params["kw"];
        $userId = $params["userId"];
        $recipients = $params["recipients"];

        $conversationIds = array();

        if ( $params["preload"] )
        {
            if ( $kw === null )
            {
                $conversations = MAILBOX_BOL_ConversationService::getInstance()->getConversationListByUserId(PEEP::getUser()->getId(), 0, 200);
            }
            else
            {
                $conversationIds = $this->conversationDao->findConversationByKeyword($kw, 16);

                $conversations = MAILBOX_BOL_ConversationService::getInstance()->getConversationItemByConversationIdList( $conversationIds );
            }
        }

        $data = $this->buildConversationData($conversations, PEEP::getLanguage()->text('mailbox', 'selector_group_other'), array($userId));

        foreach ( $data as $item )
        {
            $event->add($item);
        }
    }

    public function findUsers( $kw, $limit = null )
    {
        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
        $questionDataTable = BOL_QuestionDataDao::getInstance()->getTableName();

        $limitStr = $limit === null ? '' : 'LIMIT 0, ' . intval($limit);

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "MAILBOX_BOL_AjaxService::findUsers"
        ));

        $params = array('kw' => $kw . '%');
        $order = '';

        if ( $kw !== null )
        {
            if ( $questionName == "username" )
            {
                $order = ' ORDER BY `u`.`username`';
                $queryParts["where"] .= " AND `u`.`username` LIKE :kw";
            }
            else
            {
                $order = ' ORDER BY `qd`.`textValue`';
                $params['questionName'] = $questionName;
                $queryParts["where"] .= " AND qd.questionName=:questionName AND qd.textValue LIKE :kw";
                $queryParts['join'] .= ' INNER JOIN `' . BOL_QuestionDataDao::getInstance()->getTableName() . '` AS `qd` ON(`u`.`id` = `qd`.`userId`) ';
            }
        }

        $query = 'SELECT DISTINCT u.id FROM `' . BOL_UserDao::getInstance()->getTableName() . '` u
            '.$queryParts['join'].'
            WHERE '.$queryParts['where'] . $order . ' ' . $limitStr;

        return PEEP::getDbo()->queryForColumnList($query, $params);
    }

    protected function buildData( $userIds, $group = null, $ignoreUserIds = array() )
    {
        if ( empty($userIds) )
        {
            return array();
        }

        $infoList = MAILBOX_BOL_ConversationService::getInstance()->getUserInfoForUserIdList($userIds);

        $out = array();

        foreach ( $userIds as $userId )
        {
            if ( in_array($userId, $ignoreUserIds) )
            {
                continue;
            }

            $item = array();
            $item['id'] = 'user' . '_' . $userId;
            $item['data'] = $infoList[$userId];

            $out[] = $item;
        }

        return $out;
    }

    protected function buildDataForApi( $userIds, $group = null, $ignoreUserIds = array() )
    {
        if ( empty($userIds) )
        {
            return array();
        }

        $infoList = MAILBOX_BOL_ConversationService::getInstance()->getUserInfoForUserIdListForApi($userIds);

        $out = array();

        foreach ( $userIds as $userId )
        {
            if ( in_array($userId, $ignoreUserIds) )
            {
                continue;
            }

            $item = array();
            $item['id'] = 'user' . '_' . $userId;
            $item['data'] = $infoList[$userId];

            $out[] = $item;
        }

        return $out;
    }

    protected function buildConversationData( $conversations, $group = null, $ignoreConvIds = array() )
    {
        if ( empty($conversations) )
        {
            return array();
        }

        $out = array();

        foreach ( $conversations as $conv )
        {
            if ( in_array($conv['conversationId'], $ignoreConvIds) )
            {
                continue;
            }

            $item = array();
            $item['id'] = 'conversation' . '_' . $conv['conversationId'];
            $item['data'] = $conv;

            $out[] = $item;
        }

        return $out;
    }

    public function getSuggestEntries( $userId, $kw = null, $recipients = null, $context = 'user' )
    {
        $event = new BASE_CLASS_EventCollector('mailbox.on_search', array(
            "kw" => $kw,
            "userId" => $userId,
            "context" => $context,
            "recipients" => $recipients,
            "preload" => true
        ));

        if ( $context == 'conversation' )
        {
            $this->onConversationSearch($event);

            $out = array();

            foreach ( $event->getData() as $item )
            {
                $out[] = $item;
            }

            return array('list'=>$out, 'kw'=>$kw);
        }
        else if ($context == 'api')
        {
            $this->onSearchForApi($event);
        }
        else
        {
            $this->onSearch($event);
        }

        $out = array();

        foreach ( $event->getData() as $item )
        {
            $out[] = $item;
        }

        return $out;
    }


    public function bulkActions($data)
    {
        $userId = PEEP::getUser()->getId();

        switch($data['actionName'])
        {
            case 'markUnread':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($data['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_UNREAD);
                $message = PEEP::getLanguage()->text('mailbox', 'mark_unread_message', array('count'=>$count));
                break;
            case 'markRead':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($data['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_READ);
                $message = PEEP::getLanguage()->text('mailbox', 'mark_read_message', array('count'=>$count));
                break;
            case 'delete':
                $count = MAILBOX_BOL_ConversationService::getInstance()->deleteConversation($data['convIdList'], $userId);
                $message = PEEP::getLanguage()->text('mailbox', 'delete_message', array('count'=>$count));
                break;
        }

        return array('count'=>$count, 'message'=>$message);
    }
}
