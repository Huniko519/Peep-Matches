<?php

final class MAILBOX_BOL_ConversationService
{
    const EVENT_MARK_CONVERSATION = 'mailbox.mark_conversation';
    const EVENT_DELETE_CONVERSATION = 'mailbox.delete_conversation';

    const MARK_TYPE_READ = 'read';
    const MARK_TYPE_UNREAD = 'unread';

    const CHAT_CONVERSATION_SUBJECT = 'mailbox_chat_conversation';

    /**
     * @var MAILBOX_BOL_ConversationDao
     */
    private $conversationDao;
    /**
     * @var MAILBOX_BOL_LastMessageDao
     */
    private $lastMessageDao;
    /**
     * @var MAILBOX_BOL_MessageDao
     */
    private $messageDao;
    /**
     * @var MAILBOX_BOL_AttachmentDao
     */
    private $attachmentDao;
    /**
     * @var MAILBOX_BOL_UserLastDataDao
     */
    private $userLastDataDao;
    /**
     * @var array
     */
    private static $allowedExtensions =
        array(
            'txt', 'doc', 'docx', 'sql', 'csv', 'xls', 'ppt',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'psd', 'ai', 'pdf',
            'avi', 'wmv', 'mp3', '3gp', 'flv', 'mkv', 'mpeg', 'mpg', 'swf',
            'zip', 'gz', '.tgz', 'gzip', '7z', 'bzip2', 'rar'
        );
    /**
     * Class instance
     *
     * @var MAILBOX_BOL_ConversationService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->conversationDao = MAILBOX_BOL_ConversationDao::getInstance();
        $this->lastMessageDao = MAILBOX_BOL_LastMessageDao::getInstance();
        $this->messageDao = MAILBOX_BOL_MessageDao::getInstance();
        $this->attachmentDao = MAILBOX_BOL_AttachmentDao::getInstance();
        $this->userLastDataDao = MAILBOX_BOL_UserLastDataDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_ConversationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getUnreadMessageListForConsole( $userId, $first, $count, $lastPingTime, $ignoreList = array() )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        return $this->conversationDao->getUnreadMessageListForConsole($userId, $first, $count, $lastPingTime, $ignoreList);
    }

    /**
     * Marks conversation as Read or Unread
     *
     * @param array $conversationsId
     * @param int $userId
     * @param string $markType = self::MARK_TYPE_READ
     * @throws InvalidArgumentException
     *
     * retunn int
     */
    public function markConversation( array $conversationsId, $userId, $markType = self::MARK_TYPE_READ )
    {
        if ( empty($userId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        if ( empty($conversationsId) || !is_array($conversationsId) )
        {
            throw new InvalidArgumentException("Wrong parameter conversationsId!");
        }

        $userId = (int) $userId;
        $conversations = $this->conversationDao->findByIdList($conversationsId);

        $count = 0;

        foreach ( $conversations as $key => $value )
        {
            $conversation = &$conversations[$key];

            $lastMessages = $this->lastMessageDao->findByConversationId($conversation->id);
            if (!empty($lastMessages))
            {
                $readBy = MAILBOX_BOL_ConversationDao::READ_NONE;
                $isOpponentLastMessage = false;

                switch ( $userId )
                {
                    case $conversation->initiatorId :

                        if ( $lastMessages->initiatorMessageId < $lastMessages->interlocutorMessageId )
                        {
                            $isOpponentLastMessage = true;
                            $conversation->notificationSent = 1;
                        }

                        $readBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;

                        break;

                    case $conversation->interlocutorId :

                        if ( $lastMessages->initiatorMessageId > $lastMessages->interlocutorMessageId )
                        {
                            $isOpponentLastMessage = true;
                            $conversation->notificationSent = 1;
                        }

                        $readBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;

                        break;
                }

//                if ( !$isOpponentLastMessage )
//                {
//                    continue;
//                }

                switch ( $markType )
                {
                    case self::MARK_TYPE_READ :
                        $conversation->read = (int) $conversation->read | $readBy;
                        break;

                    case self::MARK_TYPE_UNREAD :
                        $conversation->read = (int) $conversation->read & (~$readBy);
                        break;
                }

                $this->conversationDao->save($conversation);

                if ( $this->conversationDao->getAffectedRows() > 0 )
                {
                    $count++;
                }
            }
        }

        $paramList = array(
            'conversationIdList' => $conversationsId,
            'userId' => $userId,
            'markType' => $markType);

        $event = new PEEP_Event(self::EVENT_MARK_CONVERSATION, $paramList);
        PEEP::getEventManager()->trigger($event);

        $this->resetUserLastData($userId);

        return $count;
    }

    /**
     * Marks conversation as Read
     *
     * @param array $conversationsId
     * @param int $userId
     *
     * retunn int
     */
    public function markRead( array $conversationsId, $userId )
    {
        return $this->markConversation($conversationsId, $userId, self::MARK_TYPE_READ);
    }

    /**
     * Marks message as read by recipient
     *
     * @param $messageId
     * @return bool
     */
    public function markMessageRead( $messageId )
    {
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->recipientRead = 1;
        $this->messageDao->save($message);

        return true;
    }

    public function markMessageAuthorizedToRead( $messageId )
    {
        /**
         * @var MAILBOX_BOL_Message $message
         */
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->wasAuthorized = 1;
        $this->messageDao->save($message);

        return $message;
    }

    public function markMessageAsSystem( $messageId )
    {
        /**
         * @var MAILBOX_BOL_Message $message
         */
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->isSystem = 1;
        $this->messageDao->save($message);

        return true;
    }

    /**
     * Marks conversation as Unread
     *
     * @param array $conversationsId
     * @param int $userId
     *
     * retunn int
     */
    public function markUnread( array $conversationsId, $userId )
    {
        return $this->markConversation($conversationsId, $userId, self::MARK_TYPE_UNREAD);
    }

    /**
     * Deletes conversation
     *
     * @param array $conversationsId
     * @param int $userId
     * @throws InvalidArgumentException
     *
     * return int
     */
    public function deleteConversation( array $conversationsId, $userId )
    {
        if ( empty($userId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        if ( empty($conversationsId) || !is_array($conversationsId) )
        {
            throw new InvalidArgumentException("Wrong parameter conversationsId!");
        }

        $userId = (int) $userId;
        $conversations = $this->conversationDao->findByIdList($conversationsId);

        $count = 0;

        foreach ( $conversations as $key => $value )
        {
            /**
             * @var MAILBOX_BOL_Conversation $conversation
             */
            $conversation = &$conversations[$key];

            $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_NONE;

            switch ( $userId )
            {
                case $conversation->initiatorId :
                    $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_INITIATOR;
                    $conversation->initiatorDeletedTimestamp = time();
                    break;

                case $conversation->interlocutorId :
                    $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_INTERLOCUTOR;
                    $conversation->interlocutorDeletedTimestamp = time();
                    break;
            }

            $conversation->deleted = (int) $conversation->deleted | $deletedBy;

            if ( $conversation->deleted == MAILBOX_BOL_ConversationDao::DELETED_ALL )
            {
                $this->messageDao->deleteByConversationId($conversation->id);
                $this->lastMessageDao->deleteByConversationId($conversation->id);
                $this->conversationDao->deleteById($conversation->id);
                $this->deleteAttachmentsByConversationList(array($conversation->id));

                $event = new PEEP_Event(self::EVENT_DELETE_CONVERSATION, array('conversationDto' => $conversation));
                PEEP::getEventManager()->trigger($event);
            }
            else
            {
                $this->conversationDao->save($conversation);

                // clear query cache
                switch ( $userId )
                {
                    case $conversation->initiatorId :
                        PEEP::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $conversation->initiatorId));
                        break;

                    case $conversation->interlocutorId :
                        PEEP::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $conversation->interlocutorId));
                        break;
                }
            }

            if ( $this->conversationDao->getAffectedRows() > 0 )
            {
                $count++;

                PEEP::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $userId));
            }
        }

        $this->resetUserLastData($userId);

        return $count;
    }

    /**
     * Creates new conversation
     *
     * @param int $initiatorId
     * @param int $interlocutorId
     * @param string $subject
     * @param string $text
     * @throws InvalidArgumentException
     *
     * return MAILBOX_BOL_Conversation
     */
    public function createConversation( $initiatorId, $interlocutorId, $subject, $text = '' )
    {
        if ( empty($initiatorId) || empty($interlocutorId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        $initiatorId = (int) $initiatorId;
        $interlocutorId = (int) $interlocutorId;
        $subject = trim($subject);

        if ( empty($subject) )
        {
            throw new InvalidArgumentException("Empty string params were provided!");
        }

        // create conversation
        $conversation = new MAILBOX_BOL_Conversation();
        $conversation->initiatorId = $initiatorId;
        $conversation->interlocutorId = $interlocutorId;
        $conversation->subject = $subject;
        $conversation->createStamp = time();
        $conversation->viewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

        $this->conversationDao->save($conversation);

        $text = trim($text);
        if (!empty($text))
        {
            $this->createMessage($conversation, $initiatorId, $text);
        }

        return $conversation;
    }

    public function createChatConversation( $initiatorId, $interlocutorId )
    {
        if ( empty($initiatorId) || empty($interlocutorId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        $initiatorId = (int) $initiatorId;
        $interlocutorId = (int) $interlocutorId;

        // create chat conversation
        $conversation = new MAILBOX_BOL_Conversation();
        $conversation->initiatorId = $initiatorId;
        $conversation->interlocutorId = $interlocutorId;
        $conversation->subject = self::CHAT_CONVERSATION_SUBJECT;
        $conversation->createStamp = time();
        $conversation->viewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

        $this->conversationDao->save($conversation);

        return $conversation;
    }

    /**
     * Returns conversation's messages list
     *
     * @param int $conversationId
     * @throws InvalidArgumentException
     * @return MAILBOX_BOL_Conversation
     */
    public function getConversationMessagesList( $conversationId, $first, $count )
    {
        if ( empty($conversationId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);

        $dtoList = $this->messageDao->findListByConversationId($conversationId, $count, $deletedTimestamp);
        $messageIdList = array();
        foreach($dtoList as $message)
        {
            $messageIdList[] = $message->id;
        }

        $attachmentsByMessageList = $this->findAttachmentsByMessageIdList($messageIdList);

        $list = array();
        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageData($message, $attachmentsByMessageList);
        }

        return $list;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageData( $message, $attachmentsByMessageList = null )
    {
        $item = array();

        $item['convId'] = (int)$message->conversationId;
        $item['mode'] = $this->getConversationMode((int)$message->conversationId);
        $item['id'] = (int)$message->id;
        $item['date'] = date('Y-m-d', (int)$message->timeStamp);
        $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
        $item['timeStamp'] = (int)$message->timeStamp;

        $militaryTime = (bool) PEEP::getConfig()->getValue('base', 'military_time');
        $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
        $item['recipientId'] = (int)$message->recipientId;
        $item['senderId'] = (int)$message->senderId;
        $item['isAuthor'] = (bool)((int)$message->senderId == PEEP::getUser()->getId());
        $item['recipientRead'] = (int)$message->recipientRead;
        $item['isSystem'] = (int)$message->isSystem;
        $item['attachments'] = array();

        $conversation = $this->getConversation($message->conversationId);
        if ( (int)$conversation->initiatorId == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation->interlocutorId == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        $actionName = '';
        if ($item['mode'] == 'mail')
        {
            $actionName = 'read_message';
        }

        if ($item['mode'] == 'chat')
        {
            $actionName = 'read_chat_message';
        }

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);

        $readMessageAuthorized = true;

        if ( (int)$message->senderId != PEEP::getUser()->getId() && !$message->wasAuthorized )
        {
            if ($status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE)
            {
                if ($status['authorizedBy'] == 'usercredits')
                {
                    $action = USERCREDITS_BOL_CreditsService::getInstance()->findAction('mailbox', $actionName);
                    $actionPrice = USERCREDITS_BOL_CreditsService::getInstance()->findActionPriceForUser($action->id, PEEP::getUser()->getId());

                    if ($actionPrice->amount == 0 || $actionPrice->disabled)
                    {
                        $readMessageAuthorized = true;
                        $this->markMessageAuthorizedToRead($message->id);
                    }
                    else
                    {
                        $readMessageAuthorized = false;
                        $item['isSystem'] = 1;
                        $item['previewText'] = PEEP::getLanguage()->text('mailbox', 'click_to_read_messages');
                        $text = '<p><span class="peep_small"><a href="javascript://" id="notAuthorizedMessage_'.$message->id.'" class="callReadMessage">'.PEEP::getLanguage()->text('mailbox', 'read_the_message').'</a></span></p>';
                    }
                }
                else
                {
                    $readMessageAuthorized = true;
                    $this->markMessageAuthorizedToRead($message->id);
                }

            }
            else if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
            {
                $readMessageAuthorized = false;
                $text = "<p>".$status['msg']."</p>";
                $item['previewText'] = $status['msg'];
                $item['isSystem'] = 1;
            }
            else
            {
                $readMessageAuthorized = false;
                $text = PEEP::getLanguage()->text('mailbox', $actionName.'_permission_denied');
            }
        }

        $item['readMessageAuthorized'] = $readMessageAuthorized;

        if ($readMessageAuthorized)
        {
            if ($message->isSystem)
            {
                $eventParams = json_decode($message->text, true);
                $eventParams['params']['messageId'] = (int)$message->id;

                $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                PEEP::getEventManager()->trigger($event);

                $data = $event->getData();

                if (!empty($data))
                {
                    $text = $data;
                }
                else
                {
                    $text = '<div class="peep_dialog_item odd">'.PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType'])).'</div>';
                }
            }
            else
            {
                $text = $this->splitLongMessages($message->text);
            }

            if ($attachmentsByMessageList === null)
            {
                $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
            }
            else
            {
                $attachments = array_key_exists($message->id, $attachmentsByMessageList) ? $attachmentsByMessageList[$message->id] : array();
            }

            if (!empty($attachments))
            {
                foreach($attachments as $attachment)
                {
                    $ext = UTIL_File::getExtension($attachment->fileName);
                    $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                    $attItem = array();
                    $attItem['id'] = $attachment->id;
                    $attItem['messageId'] = $attachment->messageId;
                    $attItem['downloadUrl'] = PEEP::getStorage()->getFileUrl($attachmentPath);
                    $attItem['fileName'] = $attachment->fileName;
                    $attItem['fileSize'] = $attachment->fileSize;
                    $attItem['type'] = $this->getAttachmentType($attachment);

                    $item['attachments'][] = $attItem;
                }
            }
        }

        $item['text'] = $text;

        return $item;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageDataForList( $messageList, $attachmentsByMessageList = null )
    {
        $list = array();
        $militaryTime = (bool) PEEP::getConfig()->getValue('base', 'military_time');

        $actionStatuses = array();

        $actionStatuses['read_message'] = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'read_message');
        $actionStatuses['read_chat_message'] = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'read_chat_message');

        foreach ($messageList as $message)
        {
            $conversation = $this->getConversation($message->conversationId);

            $item = array();

            $item['convId'] = (int)$message->conversationId;

            $item['mode'] = ($conversation->subject == self::CHAT_CONVERSATION_SUBJECT) ? 'chat' : 'mail';

            $item['id'] = (int)$message->id;
            $item['date'] = date('Y-m-d', (int)$message->timeStamp);
            $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
            $item['timeStamp'] = (int)$message->timeStamp;

            $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
            $item['recipientId'] = (int)$message->recipientId;
            $item['senderId'] = (int)$message->senderId;
            $item['isAuthor'] = (bool)((int)$message->senderId == PEEP::getUser()->getId());
            $item['recipientRead'] = (int)$message->recipientRead;
            $item['isSystem'] = (int)$message->isSystem;
            $item['attachments'] = array();

            if ( (int)$conversation->initiatorId == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation->interlocutorId == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            $actionName = '';
            if ($item['mode'] == 'mail')
            {
                $actionName = 'read_message';
            }

            if ($item['mode'] == 'chat')
            {
                $actionName = 'read_chat_message';
            }

            $status = $actionStatuses[$actionName];

            $readMessageAuthorized = true;

            if ( (int)$message->senderId != PEEP::getUser()->getId() && !$message->wasAuthorized )
            {
                if ($status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE)
                {
                    if ($status['authorizedBy'] == 'usercredits')
                    {
                        $action = USERCREDITS_BOL_CreditsService::getInstance()->findAction('mailbox', $actionName);
                        $actionPrice = USERCREDITS_BOL_CreditsService::getInstance()->findActionPriceForUser($action->id, PEEP::getUser()->getId());

                        if ($actionPrice->amount == 0 || $actionPrice->disabled)
                        {
                            $readMessageAuthorized = true;
                            $this->markMessageAuthorizedToRead($message->id);
                        }
                        else
                        {
                            $readMessageAuthorized = false;
                            $item['isSystem'] = 1;
                            $item['previewText'] = PEEP::getLanguage()->text('mailbox', 'click_to_read_messages');
                            $text = '<p><span class="peep_small"><a href="javascript://" id="notAuthorizedMessage_'.$message->id.'" class="callReadMessage">'.PEEP::getLanguage()->text('mailbox', 'read_the_message').'</a></span></p>';
                        }
                    }
                    else
                    {
                        $readMessageAuthorized = true;
                        $this->markMessageAuthorizedToRead($message->id);
                    }

                }
                else if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                {
                    $readMessageAuthorized = false;
                    $text = "<p>".$status['msg']."</p>";
                    $item['previewText'] = $status['msg'];
                    $item['isSystem'] = 1;
                }
                else
                {
                    $readMessageAuthorized = false;
                    $text = PEEP::getLanguage()->text('mailbox', $actionName.'_permission_denied');
                }
            }

            $item['readMessageAuthorized'] = $readMessageAuthorized;

            if ($readMessageAuthorized)
            {
                if ($message->isSystem)
                {
                    $eventParams = json_decode($message->text, true);
                    $eventParams['params']['messageId'] = (int)$message->id;

                    $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                    PEEP::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if (!empty($data))
                    {
                        $text = $data;
                    }
                    else
                    {
                        $text = '<div class="peep_dialog_item odd">'.PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType'])).'</div>';
                    }

                }
                else
                {
                    $text = $this->splitLongMessages($message->text);
                }

                if ($attachmentsByMessageList === null)
                {
                    $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
                }
                else
                {
                    $attachments = array_key_exists($message->id, $attachmentsByMessageList) ? $attachmentsByMessageList[$message->id] : array();
                }

                if (!empty($attachments))
                {
                    foreach($attachments as $attachment)
                    {
                        $ext = UTIL_File::getExtension($attachment->fileName);
                        $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                        $attItem = array();
                        $attItem['id'] = $attachment->id;
                        $attItem['messageId'] = $attachment->messageId;
                        $attItem['downloadUrl'] = PEEP::getStorage()->getFileUrl($attachmentPath);
                        $attItem['fileName'] = $attachment->fileName;
                        $attItem['fileSize'] = $attachment->fileSize;
                        $attItem['type'] = $this->getAttachmentType($attachment);

                        $item['attachments'][] = $attItem;
                    }
                }
            }

            $item['text'] = $text;

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Returns conversation info
     *
     * @param int $conversationId
     * @throws InvalidArgumentException
     * @return MAILBOX_BOL_Conversation
     */
    public function getConversation( $conversationId )
    {
        if ( empty($conversationId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        return $this->conversationDao->findById($conversationId);
    }

    /**
     * Creates New Message
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param int $senderId
     * @param string $text
     * @throws InvalidArgumentException
     */
    public function createMessage( MAILBOX_BOL_Conversation $conversation, $senderId, $text )
    {
        if ( empty($senderId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        if ( $conversation === null )
        {
            throw new InvalidArgumentException("Conversation doesn't exist!");
        }

        if ( empty($conversation->id) )
        {
            throw new InvalidArgumentException("Conversation with id = " . ($conversation->id) . " is not exist");
        }

        if ( !in_array($senderId, array($conversation->initiatorId, $conversation->interlocutorId)) )
        {
            throw new InvalidArgumentException("Wrong senderId!");
        }

        $senderId = (int) $senderId;
        $recipientId = ($senderId == $conversation->initiatorId) ? $conversation->interlocutorId : $conversation->initiatorId;

        $message = $this->addMessage($conversation, $senderId, $text);

        $event = new PEEP_Event('mailbox.send_message', array(
            'senderId' => $senderId,
            'recipientId' => $recipientId,
            'conversationId' => $conversation->id,
            'message' => $text
        ), $message);
        PEEP::getEventManager()->trigger($event);

        $this->resetUserLastData($senderId);
        $this->resetUserLastData($recipientId);

        return $message;
    }

    /**
     * @param $conversationId
     * @return MAILBOX_BOL_Message
     */
    public function getLastMessage( $conversationId )
    {
        return $this->messageDao->findLastMessage($conversationId);
    }

    public function getFirstMessage( $conversationId )
    {
        return $this->messageDao->findFirstMessage($conversationId);
    }

    public function deleteConverstionByUserId( $userId )
    {
        $count = 1000;
        $first = 0;

        if ( !empty($userId) )
        {
            $conversationList = array();

            do
            {
                $conversationList = $this->conversationDao->getConversationListByUserId($userId, $first, $count);

                $conversationIdList = array();

                foreach ( $conversationList as $conversation )
                {
                    $conversationIdList[$conversation['id']] = $conversation['id'];
                }

                if ( !empty($conversationIdList) )
                {
                    $this->conversationDao->deleteByIdList($conversationIdList);
                    $this->deleteAttachmentsByConversationList($conversationIdList);
                }

                foreach ( $conversationList as $conversation )
                {
                    $conversationIdList[$conversation['id']] = $conversation['id'];

                    $dto = new MAILBOX_BOL_Conversation();
                    $dto->id = $conversation['id'];
                    $dto->initiatorId = $conversation['initiatorId'];
                    $dto->interlocutorId = $conversation['interlocutorId'];
                    $dto->subject = $conversation['subject'];
                    $dto->read = $conversation['read'];
                    $dto->deleted = $conversation['deleted'];
                    $dto->createStamp = $conversation['createStamp'];

                    $paramList = array(
                        'conversationDto' => $dto
                    );

                    $event = new PEEP_Event(self::EVENT_DELETE_CONVERSATION, $paramList);
                    PEEP::getEventManager()->trigger($event);
                }

                $first += $count;
            }
            while ( !empty($conversationList) );
        }
    }

    public function deleteUserContent( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $this->deleteConverstionByUserId($userId);
        }
    }

    public function getConversationUrl( $conversationId, $redirectTo = null )
    {
        $params = array();
        $params['convId'] = $conversationId;

        if ( $redirectTo !== null )
        {
            $params['redirectTo'] = $redirectTo;
        }

        return PEEP::getRouter()->urlForRoute('mailbox_conversation', $params);
    }

    /**
     * @param int $initiatorId
     * @param int $interlocutorId
     * @throws InvalidArgumentException
     * @return array<MAILBOX_BOL_Conversation>
     */
    public function findConversationList( $initiatorId, $interlocutorId )
    {
        if ( empty($initiatorId) || !isset($interlocutorId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        return $this->conversationDao->findConversationList($initiatorId, $interlocutorId);
    }

    /**
     * @param MAILBOX_BOL_Conversation $conversationd
     */
    public function saveConversation( MAILBOX_BOL_Conversation $conversation )
    {
        $this->conversationDao->save($conversation);
    }

    /**
     * Add message to conversation
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param int $senderId
     * @param string $text
     * @throws InvalidArgumentException
     */
    public function addMessage( MAILBOX_BOL_Conversation $conversation, $senderId, $text )
    {
        if ( empty($senderId) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        if ( $conversation === null )
        {
            throw new InvalidArgumentException("Conversation doesn't exist!");
        }

        if ( empty($conversation->id) )
        {
            throw new InvalidArgumentException("Conversation with id = " . ($conversation->id) . " is not exist");
        }

        if ( !in_array($senderId, array($conversation->initiatorId, $conversation->interlocutorId)) )
        {
            throw new InvalidArgumentException("Wrong senderId!");
        }

        $senderId = (int) $senderId;
        $recipientId = ($senderId == $conversation->initiatorId) ? $conversation->interlocutorId : $conversation->initiatorId;

        $text = trim($text);

        if ( empty($text) )
        {
            throw new InvalidArgumentException("Empty string params were provided!");
        }

        // create message
        $message = new MAILBOX_BOL_Message();
        $message->conversationId = $conversation->id;
        $message->senderId = $senderId;
        $message->recipientId = $recipientId;
        $message->text = $text;
        $message->timeStamp = time();

        $this->messageDao->save($message);

        // insert record into LastMessage table
        $lastMessage = $this->lastMessageDao->findByConversationId($conversation->id);

        if ( $lastMessage === null )
        {
            $lastMessage = new MAILBOX_BOL_LastMessage();
            $lastMessage->conversationId = $conversation->id;
        }

        switch ( $senderId )
        {
            case $conversation->initiatorId :

                $unReadBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                $readBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INTERLOCUTOR;
                $lastMessage->initiatorMessageId = $message->id;
                $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

                break;

            case $conversation->interlocutorId :

                $unReadBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                $readBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INITIATOR;
                $lastMessage->interlocutorMessageId = $message->id;
                $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR;

                break;
        }

        $conversation->deleted = (int) $conversation->deleted & ($unDeletedBy);
        $conversation->read = ( (int) $conversation->read & (~$unReadBy) ) | $readBy;
        $conversation->viewed = $consoleViewed;
        $conversation->notificationSent = 0;

        $conversation->lastMessageId = $message->id;
        $conversation->lastMessageTimestamp = $message->timeStamp;

        $this->conversationDao->save($conversation);

        $this->lastMessageDao->save($lastMessage);

        return $message;
    }

    public function saveMessage($message)
    {
        $this->messageDao->save($message);

        return $message;
    }

    /**
     * Add Attachment files to message
     *
     * @param int $messageId
     * @param array $filesList
     */
    public function addMessageAttachments( $messageId, $fileList )
    {
        $language = PEEP::getLanguage();
        $filesCount = count($fileList);

        $configs = PEEP::getConfig()->getValues('mailbox');

        if ( empty($configs['enable_attachments']) )
        {
            return;
        }

        foreach($fileList as $file)
        {
            $dto = $file['dto'];

            $attachmentDto = new MAILBOX_BOL_Attachment();
            $attachmentDto->messageId = $messageId;
            $attachmentDto->fileName = htmlspecialchars( $dto->origFileName );
            $attachmentDto->fileSize = $dto->size;
            $attachmentDto->hash = uniqid();

            $this->addAttachment($attachmentDto, $file['path']);
        }
    }

    /**
     * Add attachment
     *
     * @param MAILBOX_BOL_Attachment $attachmentDto
     * @param string $filePath
     * @param boolean
     */
    public function addAttachment( $attachmentDto, $filePath )
    {
        $this->attachmentDao->save($attachmentDto);

        $attId = $attachmentDto->id;
        $ext = UTIL_File::getExtension($attachmentDto->fileName);

        $attachmentPath = $this->getAttachmentFilePath($attId, $attachmentDto->hash, $ext, $attachmentDto->fileName);
        $pluginFilesPath = PEEP::getPluginManager()->getPlugin('mailbox')->getPluginFilesDir() . uniqid('attach');

        $storage = PEEP::getStorage();
        if ( $storage->fileExists($filePath) )
        {
            $storage->renameFile($filePath, $attachmentPath);
            @unlink($pluginFilesPath);
            @unlink($filePath);

            return true;
        }
        else
        {
            $this->attachmentDao->deleteById($attId);
            return false;
        }
    }

    public function getAttachmentType(MAILBOX_BOL_Attachment $attachment)
    {
        $type = 'doc';

        if (UTIL_File::validateImage($attachment->fileName))
        {
            $type = 'image';
        }

        return $type;
    }

    public function getAttachmentFilePath( $attId, $hash, $ext, $name = null )
    {
        return $this->getAttachmentDir() . $this->getAttachmentFileName($attId, $hash, $ext, $name);
    }

    public function getAttachmentDir()
    {
        return PEEP::getPluginManager()->getPlugin('mailbox')->getUserFilesDir() . 'attachments' . DS;
    }

    public function getAttachmentUrl()
    {
        return PEEP::getPluginManager()->getPlugin('mailbox')->getUserFilesUrl() . 'attachments/';
    }

    public function getAttachmentFileName( $attId, $hash, $ext, $name )
    {
        $lastAttId = 0;
        if (PEEP::getConfig()->configExists('mailbox', 'last_attachment_id'))
        {
            $lastAttId = (int)PEEP::getConfig()->getValue('mailbox', 'last_attachment_id');
        }

        if ($attId <= $lastAttId)
        {
            return 'attachment_' . $attId . '_' . $hash . (strlen($ext) ? '.' . $ext : '');
        }

        return 'attachment_' . $attId . '_' . $hash . (mb_strlen($name) ? '_' . $name : (strlen($ext) ? '.' . $ext : ''));
    }

    public function fileExtensionIsAllowed( $ext )
    {
        if ( !strlen($ext) )
        {
            return false;
        }

        return in_array($ext, self::$allowedExtensions);
    }

    /**
     *
     * @param array $messageIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function findAttachmentsByMessageIdList( array $messageIdList )
    {
        $result = array();
        $list = $this->attachmentDao->findAttachmentsByMessageIdList($messageIdList);
        foreach ($list as $attachment)
        {
            $result[$attachment->messageId][] = $attachment;
        }

        return $result;
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function getAttachmentsCountByConversationList( array $conversationIdList )
    {
        return $this->attachmentDao->getAttachmentsCountByConversationList($conversationIdList);
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function deleteAttachmentsByConversationList( array $conversationIdList )
    {
        $attachmentList = $this->attachmentDao->findAttachmentstByConversationList($conversationIdList);

        foreach ( $attachmentList as $attachment )
        {
            $ext = UTIL_File::getExtension($attachment['fileName']);
            $path = $this->getAttachmentFilePath($attachment['id'], $attachment['hash'], $ext, $attachment['fileName']);

            if ( PEEP::getStorage()->removeFile($path) )
            {
                $this->attachmentDao->deleteById($attachment['id']);
            }
        }
    }

    /**
     * Do not call this method
     * This is a temporary method used for mailbox plugin update.
     *
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function convertHtmlTags()
    {
        if ( !PEEP::getConfig()->configExists('mailbox', 'update_to_revision_7200') )
        {
            return;
        }

        $lastId = PEEP::getConfig()->getValue('mailbox', 'last_updated_id');
        $messageList = $this->messageDao->findNotUpdatedMessages($lastId, 2000);

        if ( empty($messageList) )
        {
            PEEP::getConfig()->deleteConfig('mailbox', 'update_to_revision_7200');
            PEEP::getConfig()->deleteConfig('mailbox', 'last_updated_id');
            return;
        }

        $count = 0;

        foreach ( $messageList as $message )
        {
            $message->text = preg_replace("/\n/", "", $message->text);
            $message->text = preg_replace("/<br \/>/", "\n", $message->text);
            $message->text = strip_tags($message->text);

            $this->messageDao->save($message);
            $count++;

            if ( $count > 100 )
            {
                PEEP::getConfig()->saveConfig('mailbox', 'last_updated_id', $message->id);
            }
        }

        PEEP::getConfig()->saveConfig('mailbox', 'last_updated_id', $message->id);
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Conversation>
     */
    public function getConversationListByIdList( $idList )
    {
        return $this->conversationDao->findByIdList($idList);
    }

    public function setConversationViewedInConsole( $idList, $userId )
    {
        $conversationList = $this->getConversationListByIdList($idList);
        /* @var $conversation MAILBOX_BOL_Conversation  */
        foreach ( $conversationList as $conversation )
        {
            if ( $conversation->initiatorId == $userId )
            {
                $conversation->viewed = $conversation->viewed | MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;
            }

            if ( $conversation->interlocutorId == $userId )
            {
                $conversation->viewed = $conversation->viewed | MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR;
            }

            $this->saveConversation($conversation);
        }

        $this->resetUserLastData($userId);
    }

    public function getConversationListForConsoleNotificationMailer( $userIdList )
    {
        return $this->conversationDao->getNewConversationListForConsoleNotificationMailer($userIdList);
    }

    public function getConversationPreviewText($conversation)
    {
        $convPreview = '';

        switch($conversation['mode'])
        {
            case 'mail':

                $convPreview = ($conversation['subject'] == MAILBOX_BOL_ConversationDao::WINK_CONVERSATION_SUBJECT) ? PEEP::getLanguage()->text('mailbox', 'wink_conversation_subject') : $conversation['subject'];

                break;

            case 'chat':

                $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'read_chat_message');

                $readMessageAuthorized = true;

                if ( (int)$conversation['lastMessageSenderId'] != PEEP::getUser()->getId() && !$conversation['lastMessageWasAuthorized'])
                {
                    if ($status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE)
                    {
                        if ($status['authorizedBy'] == 'usercredits')
                        {
                            $readMessageAuthorized = false;
                            $convPreview = PEEP::getLanguage()->text('mailbox', 'click_to_read_messages');
                        }
                        else
                        {
                            $readMessageAuthorized = true;
                            $this->markMessageAuthorizedToRead($conversation['lastMessageId']);
                        }
                    }
                    else if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
                    {
                        $readMessageAuthorized = false;
                        $convPreview = $status['msg'];
                    }
                    else
                    {
                        $readMessageAuthorized = false;
                        $convPreview = PEEP::getLanguage()->text('mailbox', 'read_permission_denied');
                    }
                }

                if ($readMessageAuthorized)
                {
                    if ($conversation['isSystem'])
                    {
                        $eventParams = json_decode($conversation['text'], true);
                        $eventParams['params']['messageId'] = (int)$conversation['lastMessageId'];
                        $eventParams['params']['getPreview'] = true;

                        $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                        PEEP::getEventManager()->trigger($event);

                        $data = $event->getData();

                        if (!empty($data))
                        {
                            $convPreview = $data;
                        }
                        else
                        {
                            $convPreview = PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']));
                        }
                    }
                    else
                    {
                        $short = mb_strlen($conversation['text']) > 50 ? mb_substr($conversation['text'], 0, 50) . '...' : $conversation['text'];
//                        $short = UTIL_HtmlTag::autoLink($short);

                        $event = new PEEP_Event('mailbox.message_render', array(
                            'conversationId' => $conversation['id'],
                            'messageId' => $conversation['lastMessageId'],
                            'senderId' => $conversation['lastMessageSenderId'],
                            'recipientId' => $conversation['lastMessageRecipientId'],
                        ), array( 'short' => $short, 'full' => $conversation['text'] ));

                        PEEP::getEventManager()->trigger($event);

                        $eventData = $event->getData();

                        $convPreview = $eventData['short'];
                    }
                }

                break;
        }

        return $convPreview;
    }


    public function getConversationPreviewTextForApi($conversation)
    {
        $convPreview = '';

        switch($conversation['mode']) {
            case 'mail':
                $authActionName = 'read_message';
                break;

            case 'chat':
                $authActionName = 'read_chat_message';
                break;
        }

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $authActionName);

        $readMessageAuthorized = true;

        if ( (int)$conversation['lastMessageSenderId'] != PEEP::getUser()->getId() && !$conversation['lastMessageWasAuthorized'])
        {
            if ($status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE)
            {
                if ($status['authorizedBy'] == 'usercredits')
                {
                    $readMessageAuthorized = false;
                    $convPreview = PEEP::getLanguage()->text('mailbox', 'click_to_read_messages');
                }
                else
                {
                    $readMessageAuthorized = true;
                    $this->markMessageAuthorizedToRead($conversation['lastMessageId']);
                }
            }
            else if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
            {
                $readMessageAuthorized = false;
                $convPreview = UTIL_HtmlTag::stripTags($status['msg']);
            }
            else
            {
                $readMessageAuthorized = false;
                $convPreview = PEEP::getLanguage()->text('mailbox', 'read_permission_denied');
            }
        }

        if ($readMessageAuthorized)
        {
            if ($conversation['isSystem'])
            {
                $eventParams = json_decode($conversation['text'], true);
                $eventParams['params']['messageId'] = (int)$conversation['lastMessageId'];
                $eventParams['params']['getPreview'] = true;

                $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                PEEP::getEventManager()->trigger($event);

                $data = $event->getData();

                if (!empty($data))
                {
                    $convPreview = $data;
                }
                else
                {
                    $convPreview = PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']));
                }
            }
            else
            {
                $short = mb_strlen($conversation['text']) > 50 ? mb_substr($conversation['text'], 0, 50) . '...' : $conversation['text'];
//                        $short = UTIL_HtmlTag::autoLink($short);

                $event = new PEEP_Event('mailbox.message_render', array(
                    'conversationId' => $conversation['id'],
                    'messageId' => $conversation['lastMessageId'],
                    'senderId' => $conversation['lastMessageSenderId'],
                    'recipientId' => $conversation['lastMessageRecipientId'],
                ), array( 'short' => $short, 'full' => $conversation['text'] ));

                PEEP::getEventManager()->trigger($event);

                $eventData = $event->getData();

                $convPreview = $eventData['short'];
            }
        }

        return $convPreview;
    }

    public function getConversationItem($mode, $convId)
    {
        $userId = PEEP::getUser()->getId();

        $conversation = $this->conversationDao->getConversationItem($convId);

        $conversationRead = 0;
        $conversationHasReply = false;

        switch ( $userId )
        {
            case $conversation['initiatorId']:

                $conversationOpponentId = $conversation['interlocutorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                {
                    $conversationRead = 1;
                }

                break;

            case $conversation['interlocutorId']:

                $conversationOpponentId = $conversation['initiatorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                {
                    $conversationRead = 1;
                }

                break;
        }

        switch($userId)
        {
            case $conversation['lastMessageSenderId']:
                $conversationHasReply = false;
                break;

            case $conversation['lastMessageRecipientId']:
                $conversationHasReply = true;
                break;
        }

        $conversation['opponentId'] = $conversationOpponentId;
        $conversation['conversationRead'] = $conversationRead;
        $conversation['mode'] = $mode;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($conversationOpponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($conversationOpponentId) : $profileDisplayname;
        $profileUrl = BOL_UserService::getInstance()->getUserUrl($conversationOpponentId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($conversationOpponentId);
        $profileAvatarUrl = empty($avatarUrl) ? BOL_AvatarService::getInstance()->getDefaultAvatarUrl() : $avatarUrl;
        $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);

        $convPreview = $this->getConversationPreviewText($conversation);

        $item = array();

        $item['conversationId'] = (int)$convId;
        $item['opponentId'] = (int)$conversationOpponentId;
        $item['mode'] = $mode;
        $item['conversationRead'] = (int)$conversationRead;
        $item['profileUrl'] = $profileUrl;
        $item['avatarUrl'] = $profileAvatarUrl;

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($conversationOpponentId));

        $item['avatarLabel'] = !empty($avatarData[$conversationOpponentId]) ? mb_substr($avatarData[$conversationOpponentId]['label'], 0, 1) : ' ';

        $item['displayName'] = $profileDisplayname;
        $item['dateLabel'] = $convDate;
        $item['previewText'] = $convPreview;
        $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
        $item['reply'] = $conversationHasReply;
        $item['newMessageCount'] = $this->countUnreadMessagesForConversation($convId, $userId);

        if ( (int)$conversation['initiatorId'] == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation['interlocutorId'] == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        if ($mode == 'chat')
        {
            $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$conversationOpponentId));
        }

        if ($mode == 'mail')
        {
            $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
        }


        return $item;
    }

    public function getConversationItemByConversationIdList($conversationItemList)
    {
        $userId = PEEP::getUser()->getId();
        $convInfoList = array();

        $userIdList = array();
        $conversationIdList = array();
        foreach($conversationItemList as $conversation)
        {
            $conversationIdList[] = (int)$conversation['id'];

            if ($conversation['interlocutorId'] == $userId)
            {
                $opponentId = $conversation['initiatorId'];
            }
            else
            {
                $opponentId = $conversation['interlocutorId'];
            }

            if (!in_array($opponentId, $userIdList))
            {
                $userIdList[] = $opponentId;
            }
        }

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $unreadMessagesCountByConversationIdList = $this->countUnreadMessagesForConversationList($conversationIdList, $userId);
        $conversationsWithAttachments = $this->getConversationsWithAttachmentFromConversationList($conversationIdList);

        foreach($conversationItemList as $conversation)
        {
            $conversationId = (int)$conversation['id'];
            $mode = $conversation['subject'] == self::CHAT_CONVERSATION_SUBJECT ? 'chat' : 'mail';

            $conversationRead = 0;
            $conversationHasReply = false;

            switch ( $userId )
            {
                case $conversation['initiatorId']:

                    $opponentId = $conversation['interlocutorId'];
//                    $conversationHasReply = $conversation['interlocutorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $conversationRead = 1;
                    }

                    break;

                case $conversation['interlocutorId']:

                    $opponentId = $conversation['initiatorId'];
//                    $conversationHasReply = $conversation['initiatorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $conversationRead = 1;
                    }

                    break;
            }

//            pv($conversation);

            switch($userId)
            {
                case $conversation['lastMessageSenderId']:
                    $conversationHasReply = false;
                    break;

                case $conversation['lastMessageRecipientId']:
                    $conversationHasReply = true;
                    break;
            }

            $conversation['opponentId'] = $opponentId;
            $conversation['conversationRead'] = $conversationRead;
            $conversation['mode'] = $mode;

            $profileDisplayname = empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'];
            $profileUrl = $avatarData[$opponentId]['url'];
            $avatarUrl = $avatarData[$opponentId]['src'];
            $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);
            $convPreview = $this->getConversationPreviewText($conversation);

            $item = array();

            $item['conversationId'] = $conversationId;
            $item['opponentId'] = (int)$opponentId;
            $item['mode'] = $mode;
            $item['conversationRead'] = (int)$conversationRead;
            $item['profileUrl'] = $profileUrl;
            $item['avatarUrl'] = $avatarUrl;
            $item['avatarLabel'] = !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null;
            $item['displayName'] = $profileDisplayname;
            $item['dateLabel'] = $convDate;
            $item['previewText'] = $convPreview;
            $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
            $item['reply'] = $conversationHasReply;
            $item['newMessageCount'] = array_key_exists($conversationId, $unreadMessagesCountByConversationIdList) ? $unreadMessagesCountByConversationIdList[$conversationId] : 0;
            $item['hasAttachment'] = $conversationsWithAttachments[$conversationId];

            $shortUserData = $this->getFields(array($opponentId));
            $item['shortUserData'] = $shortUserData[$opponentId];

            if ( (int)$conversation['initiatorId'] == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation['interlocutorId'] == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            if ($mode == 'chat')
            {
                $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }

            if ($mode == 'mail')
            {
                $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$conversationId));
            }

            $convInfoList[] = $item;
        }


        return $convInfoList;
    }

    public function getConversationItemByConversationIdListForApi($conversationItemList)
    {
        $userId = PEEP::getUser()->getId();
        $convInfoList = array();

        $userIdList = array();
        $conversationIdList = array();
        foreach($conversationItemList as $conversation)
        {
            $conversationIdList[] = (int)$conversation['id'];

            if ($conversation['interlocutorId'] == $userId)
            {
                $opponentId = $conversation['initiatorId'];
            }
            else
            {
                $opponentId = $conversation['interlocutorId'];
            }

            if (!in_array($opponentId, $userIdList))
            {
                $userIdList[] = $opponentId;
            }
        }

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, false, true, true);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $unreadMessagesCountByConversationIdList = $this->countUnreadMessagesForConversationList($conversationIdList, $userId);
        $conversationsWithAttachments = $this->getConversationsWithAttachmentFromConversationList($conversationIdList);
        $onlineMap = BOL_UserService::getInstance()->findOnlineStatusForUserList($userIdList);

        foreach($conversationItemList as $conversation)
        {
            $conversationId = (int)$conversation['id'];
            $mode = $conversation['subject'] == self::CHAT_CONVERSATION_SUBJECT ? 'chat' : 'mail';

            $conversationRead = 0;
            $conversationHasReply = false;

            switch ( $userId )
            {
                case $conversation['initiatorId']:

                    $opponentId = $conversation['interlocutorId'];
//                    $conversationHasReply = $conversation['interlocutorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $conversationRead = 1;
                    }

                    break;

                case $conversation['interlocutorId']:

                    $opponentId = $conversation['initiatorId'];
//                    $conversationHasReply = $conversation['initiatorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $conversationRead = 1;
                    }

                    break;
            }

//            pv($conversation);

            switch($userId)
            {
                case $conversation['lastMessageSenderId']:
                    $conversationHasReply = false;
                    break;

                case $conversation['lastMessageRecipientId']:
                    $conversationHasReply = true;
                    break;
            }

            $conversation['opponentId'] = $opponentId;
            $conversation['conversationRead'] = $conversationRead;
            $conversation['mode'] = $mode;

            $profileDisplayname = empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'];
//            $profileUrl = $avatarData[$opponentId]['url'];
            $avatarUrl = $avatarData[$opponentId]['src'];
            $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);
            $convPreview = $this->getConversationPreviewTextForApi($conversation);

            $item = array();

            $item['userId'] = (int)$opponentId; // Backward compatibility
            $item['conversationId'] = $conversationId;
            $item['opponentId'] = (int)$opponentId;
            $item['mode'] = $mode;
            $item['conversationRead'] = (int)$conversationRead;
//            $item['profileUrl'] = $profileUrl;
            $item['avatarUrl'] = $avatarUrl;
            $item['avatarLabel'] = !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null;
            $item['displayName'] = $profileDisplayname;
            $item['dateLabel'] = $convDate;
            $item['previewText'] = $convPreview;
            $item['subject'] = $conversation['subject'];
            $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
            $item['reply'] = $conversationHasReply;
            $item['newMessageCount'] = array_key_exists($conversationId, $unreadMessagesCountByConversationIdList) ? $unreadMessagesCountByConversationIdList[$conversationId] : 0;
            $item['hasAttachment'] = $conversationsWithAttachments[$conversationId];

            $item['timeLabel'] = $conversation["timeStamp"] > 0 ? UTIL_DateTime::formatDate($conversation["timeStamp"]) : "";
            $item['onlineStatus'] = $onlineMap[$opponentId];

            $winkReceived = PEEP::getEventManager()->call('winks.isWinkSent', array('userId'=>$userId, 'partnerId'=>$opponentId));
            $item['winkReceived'] = (int)$winkReceived;

            $shortUserData = $this->getFields(array($opponentId));
            $item['shortUserData'] = $shortUserData[$opponentId];

            if ( (int)$conversation['initiatorId'] == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation['interlocutorId'] == PEEP::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            if ($mode == 'chat')
            {
                $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }

            if ($mode == 'mail')
            {
                $item['url'] = PEEP::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$conversationId));
            }

            $convInfoList[] = $item;
        }


        return $convInfoList;
    }

    /**
     * @param $messageId
     * @return MAILBOX_BOL_Message
     */
    public function getMessage($messageId)
    {
        return $this->messageDao->findById($messageId);
    }

    public function getChatConversationIdWithUserById($userId, $opponentId)
    {
        return $this->conversationDao->findChatConversationIdWithUserById($userId, $opponentId);
    }

    public function getChatConversationIdWithUserByIdList($userId, $userIdList)
    {
        $result = array();

        $conversationIdList = $this->conversationDao->findChatConversationIdWithUserByIdList($userId, $userIdList);

        foreach($conversationIdList as $conversationInfo)
        {
            $result[$conversationInfo['opponentId']] = $conversationInfo['id'];
        }

        return $result;
    }

    public function getWinkConversationIdWithUserById($userId, $opponentId)
    {
        return $this->conversationDao->findWinkConversationIdWithUserById($userId, $opponentId);
    }

    public function getConversationMode($conversationId)
    {
        $mode = 'mail';

        $conversation = $this->getConversation($conversationId);

        if ($conversation->subject == self::CHAT_CONVERSATION_SUBJECT)
        {
            $mode = 'chat';
        }

        return $mode;
    }

    public function getUserStatus($userId)
    {
        $userIdList = array($userId);

        $onlineInfo = $this->getUserStatusForUserIdList($userIdList);

        return $onlineInfo[$userId];
    }

    public function getUserStatusForUserIdList($userIdList)
    {
        $onlineInfo = array();
        $list = BOL_UserService::getInstance()->findOnlineStatusForUserList($userIdList);
        $privacyForUserIdList = $this->getViewPresenceOnSitePrivacySettingsForUserIdList( PEEP::getUser()->getId(), $userIdList );

        foreach($list as $userId => $status)
        {
            $viewPresenceOnSiteAllowed = $privacyForUserIdList[$userId];

            if ($viewPresenceOnSiteAllowed && $status > 0)
            {
                switch($status)
                {
                    case BOL_UserOnlineDao::CONTEXT_VAL_DESKTOP:
                        $onlineInfo[$userId] = 'status_online';
                        break;
                    case BOL_UserOnlineDao::CONTEXT_VAL_MOBILE:
                        $onlineInfo[$userId] = 'status_mobile';
                        break;
                    default:
                        $onlineInfo[$userId] = 'status_online';
                        break;
                }
            }
            else
            {
                $onlineInfo[$userId] = 'offline';
            }
        }

        return $onlineInfo;
    }

    public function getConversationHistory($conversationId, $beforeMessageId)
    {
        $count = 10;
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);
        $dtoList = $this->messageDao->findHistory($conversationId, $beforeMessageId, $count, $deletedTimestamp);
        $list = array();
        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageData($message);
        }

        $data = array(
            'log' => $list
        );

        return $data;
    }

    public function getConversationHistoryForApi($conversationId, $beforeMessageId)
    {
        $count = 10;
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);
        $dtoList = $this->messageDao->findHistory($conversationId, $beforeMessageId, $count, $deletedTimestamp);
        $list = array();
        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageDataForApi($message);
        }

        $data = array(
            'log' => $list
        );

        return $data;
    }

    public function getConversationDataAndLog($conversationId, $first = 0, $count = 16)
    {
        $userId = PEEP::getUser()->getId();
        $conversation = $this->getConversation($conversationId);
        if (empty($conversation))
        {
            return array();
        }

        if ($conversation->initiatorId != $userId && $conversation->interlocutorId != $userId)
        {
            return array('close_dialog'=>true);
        }

        $list = $this->getConversationMessagesList($conversationId, $first, $count);
        $language = PEEP::getLanguage();

        switch ( $userId )
        {
            case $conversation->initiatorId:

                $conversationOpponentId = (int)$conversation->interlocutorId;

                break;

            case $conversation->interlocutorId:

                $conversationOpponentId = (int)$conversation->initiatorId;

                break;
        }

        $data = array();
        $data['conversationId'] = $conversationId;
        $data['opponentId'] = $conversationOpponentId;
        $data['mode'] = $this->getConversationMode($conversationId);
        $data['subject'] = ($conversation->subject == MAILBOX_BOL_ConversationDao::WINK_CONVERSATION_SUBJECT) ? PEEP::getLanguage()->text('mailbox', 'wink_conversation_subject') : $conversation->subject;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($conversationOpponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($conversationOpponentId) : $profileDisplayname;
        $data['displayName'] = $profileDisplayname;
        $data['profileUrl'] = BOL_UserService::getInstance()->getUserUrl($conversationOpponentId);

        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($conversationOpponentId);
        $data['avatarUrl'] = empty($avatarUrl) ? BOL_AvatarService::getInstance()->getDefaultAvatarUrl() : $avatarUrl;

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($conversationOpponentId));
        $data['avatarLabel'] = !empty($avatarData[$conversationOpponentId]) ? mb_substr($avatarData[$conversationOpponentId]['label'], 0, 1) : null;

        $data['status'] = $this->getUserStatus($conversationOpponentId);
        $data['log'] = $list;
        $data['logLength'] = $this->getConversationLength($conversationId);
        $shortUserData = $this->getFields(array($conversationOpponentId));
        $data['shortUserData'] = $shortUserData[$conversationOpponentId];

        $checkResult = $this->checkUser($userId, $conversationOpponentId);

        $data['isSuspended'] = $checkResult['isSuspended'];
        if ($data['isSuspended'])
        {
            $data['suspendReasonMessage'] = $checkResult['suspendReasonMessage'];
        }

        return $data;
    }

    /**
     * @param $userId
     * @return MAILBOX_BOL_Message
     */
    public function getLastSentMessage($userId)
    {
        return $this->messageDao->findLastSentMessage($userId);
    }

    public function findUnreadMessages( $userId, $ignoreList, $timeStamp = null )
    {
        $list = array();

        $messages = $this->messageDao->findUnreadMessages($userId, $ignoreList, $timeStamp, $this->getActiveModeList());

        $list = $this->getMessageDataForList($messages);

        return $list;
    }

    public function splitLongMessages($string)
    {
        return $string;

        $split_length = 100;
        $delimiter = ' ';
        $string_array = explode(' ', $string);

        foreach ( $string_array as $id => $word )
        {
            if ( mb_strlen(trim($word)) > $split_length )
            {
                $originalWord = $word;
                $autoLinked = UTIL_HtmlTag::autoLink(trim($word));
                if ( strlen( $autoLinked ) != strlen( trim($originalWord) ) )
                {
//                    $str = mb_substr($originalWord, $split_length);
//                    $str = $this->splitLongMessages($str);
                    $string_array[$id] = $originalWord;// '<a href="'.$originalWord.'" target="_blank">'.mb_substr($originalWord, 7, $split_length) . $delimiter . $str."</a>";
                }
                else
                {
                    $str = mb_substr($word, $split_length);
                    $str = $this->splitLongMessages($str);
                    $string_array[$id] = mb_substr($word, 0, $split_length) . $delimiter . $str;
                }
            }
        }

        return implode(' ', $string_array);
    }

    public function markMessageIdListRead($messageIdList)
    {
        $this->markMessageIdListReadByUser($messageIdList, PEEP::getUser()->getId());
    }

    public function markMessageIdListReadByUser($messageIdList, $userId)
    {
        $conversationIds = array();
        foreach($messageIdList as $messageId)
        {
            $message = $this->getMessage($messageId);
            if (!in_array($message->conversationId, $conversationIds))
            {
                $conversationIds[] = $message->conversationId;
            }

            $this->markMessageRead($messageId);

        }

        $this->markRead($conversationIds, $userId);
    }

    private function getUserIdListAlt($userId)
    {
        $friendsEnabled = (bool)PEEP::getEventManager()->call('plugin.friends');
        if ($friendsEnabled)
        {
            $friendIdList = PEEP::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        }
        else
        {
            $friendIdList = array();
        }

        $userIdList = array();

        $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findList"
        ));

        $correspondenceCondition = "";
        $friendsCondition = "";
        if (!empty($userWithCorrespondenceIdList))
        {
            $friendsCondition = "";
            if (!empty($friendIdList))
            {
                $correspondenceCondition = " AND ( `u`.`id` IN ( ".PEEP::getDbo()->mergeInClause($userWithCorrespondenceIdList)." ) ";
                $friendsCondition = " OR `u`.`id` IN ( ".PEEP::getDbo()->mergeInClause($friendIdList)." ) )";
            }
            else
            {
                $correspondenceCondition = " AND `u`.`id` IN ( ".PEEP::getDbo()->mergeInClause($userWithCorrespondenceIdList)." ) ";
            }
        }
        else
        {
            if (!empty($friendIdList))
            {
                $friendsCondition = " AND `u`.`id` IN ( ".PEEP::getDbo()->mergeInClause($friendIdList)." )";
            }
            else
            {
                return array(
                    'userIdList' => $userIdList,
                    'userWithCorrespondenceIdList' => $userWithCorrespondenceIdList,
                    'friendIdList' => $friendIdList
                );
            }
        }

        $query = "SELECT `u`.`id`
            FROM `".BOL_UserDao::getInstance()->getTableName()."` as `u`
            {$queryParts["join"]}

            WHERE {$queryParts["where"]} ".$correspondenceCondition." ".$friendsCondition;

        $tmpUserIdList = PEEP::getDbo()->queryForColumnList($query);

        foreach($tmpUserIdList as $id)
        {
            if ($id == $userId) continue;

            if (!in_array($id, $userIdList))
            {
                $userIdList[] = $id;
            }
        }

        return array(
            'userIdList' => $userIdList,
            'userWithCorrespondenceIdList' => $userWithCorrespondenceIdList,
            'friendIdList' => $friendIdList
        );
    }

    private function getUserIdList($userId)
    {
        return $this->getUserIdListAlt($userId);

        $friendsEnabled = (bool)PEEP::getEventManager()->call('plugin.friends');
        if ($friendsEnabled)
        {
            $friendIdList = PEEP::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        }
        else
        {
            $friendIdList = array();
        }

        $userIdList = array();

        $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
        foreach($userWithCorrespondenceIdList as $id)
        {
            $userIdList[] = $id;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findList"
        ));

        $correspondenceCondition = "";
        if (!empty($userWithCorrespondenceIdList))
        {
            $correspondenceCondition = " AND `u`.`id` NOT IN ( ".PEEP::getDbo()->mergeInClause($userWithCorrespondenceIdList)." ) ";
        }

        $query = "SELECT `u`.`id`
            FROM `".BOL_UserDao::getInstance()->getTableName()."` as `u`
            {$queryParts["join"]}

            WHERE {$queryParts["where"]} ".$correspondenceCondition;

        $tmpUserIdList = PEEP::getDbo()->queryForColumnList($query);

        foreach($tmpUserIdList as $id)
        {
            if ($id == $userId) continue;

            if (!in_array($id, $userIdList))
            {
                $userIdList[] = $id;
            }
        }

        return array(
            'userIdList' => $userIdList,
            'userWithCorrespondenceIdList' => $userWithCorrespondenceIdList,
            'friendIdList' => $friendIdList
        );
    }

    public function getUserList($userId, $data = array())
    {
        if (empty($data))
        {
            $data = $this->getUserIdList($userId);
        }
        $list = $this->getUserInfoForUserIdList($data['userIdList'], $data['userWithCorrespondenceIdList'], $data['friendIdList']);
        $onlineCount = 0;
        $result = array();
        foreach($list as $userData)
        {
            $result[] = $userData;
            if ($userData['status'] != 'offline' && empty($userData['wasBlocked']))
            {
                $onlineCount++;
            }
        }
        return array('onlineCount'=>$onlineCount, 'list'=>$result);
    }

    public function getUserOnlineList($userId)
    {
        $data = $this->getUserIdList($userId);

        $list = $this->getUserOnlineInfoForUserIdList($data['userIdList'], $data['userWithCorrespondenceIdList'], $data['friendIdList']);

        $onlineCount = 0;
        $result = array();
        foreach($list as $userData)
        {
            $result[] = $userData;
            if ($userData['status'] != 'offline' && empty($userData['wasBlocked']))
            {
                $onlineCount++;
            }
        }

        return array('onlineCount'=>$onlineCount, 'list'=>$result, 'userIdList'=>$data);
    }

    public function resetUserLastData($userId)
    {
        $userLastData = $this->userLastDataDao->findUserLastDataFor($userId);

        if ($userLastData)
        {
            $userLastData->data = '';

            $this->userLastDataDao->save($userLastData);
        }
    }

    public function resetAllUsersLastData()
    {
        $example = new PEEP_Example();
        $example->andFieldNotEqual('userId', 0);
        $this->userLastDataDao->deleteByExample($example);
    }

    public function getLastData($params)
    {
        return $this->getLastDataAlt($params);

        $result = array();
        $userId = PEEP::getUser()->getId();
        $userService = BOL_UserService::getInstance();

        $userOnlineListData = $this->getUserOnlineList($userId);

        if ($params['userOnlineCount'] === 0 || $userOnlineListData['onlineCount'] != $params['userOnlineCount'])
        {
            $userListData = $this->getUserList($userId, $userOnlineListData['userIdList']);
            $result['userList'] = $userListData['list'];
            $result['userOnlineCount'] = $userListData['onlineCount'];
        }

        $messageList = $this->findUnreadMessages($userId, $params['unreadMessageList'], $params['lastMessageTimestamp']);
        if (!empty($messageList))
        {
            $result['messageList'] = $messageList;
        }

        $convListLength = $this->countConversationListByUserId($userId);

        if ($convListLength != $params['conversationsCount'])
        {
            $result['conversationsCount'] = $convListLength;
            $result['convList'] = $this->getConversationListByUserId(PEEP::getUser()->getId(), 0, 10);
        }

        return $result;
    }

    public function getLastDataAlt($params)
    {
        $result = array();
        $userId = PEEP::getUser()->getId();

        $userLastData = $this->userLastDataDao->findUserLastDataFor($userId);

        if (empty($userLastData))
        {
            $userLastData = new MAILBOX_BOL_UserLastData();
            $userLastData->userId = $userId;
        }

        if ($userLastData->data == '')
        {
            $userData = array();
            $userService = BOL_UserService::getInstance();

            $userOnlineListData = $this->getUserOnlineList($userId);

            $userListData = $this->getUserList($userId, $userOnlineListData['userIdList']);

            $userData['userOnlineCount'] = $userListData['onlineCount'];
            $userData['userList'] = $userListData['list'];

//            $messageList = $this->findUnreadMessages($userId, $params['unreadMessageList'], $params['lastMessageTimestamp']);
//            if (!empty($messageList))
//            {
//                $conversations = array();
//                $notViewedConversations = 0;
//                foreach($messageList as $message)
//                {
//                    if (!in_array($message['convId'], $conversations))
//                    {
//                        $conversations[] = $message['convId'];
//                        if (!$message['conversationViewed'])
//                        {
//                            $notViewedConversations++;
//                        }
//                    }
//                }
//                $userData['messageList'] = $messageList;
//                $userData['newMessageCount'] = array('all'=>count($conversations), 'new'=>(int)$notViewedConversations);
//            }
//            else
//            {
//                $userData['messageList'] = '';
//                $userData['newMessageCount'] = array('all'=>0, 'new'=>0);//TODO
//            }

            
            $userData['conversationsCount'] = $this->countConversationListByUserId($userId);
            $userData['convList'] = $this->getConversationListByUserId(PEEP::getUser()->getId(), 0, 10); //TODO get limits from client side

        
            $userLastData->data = json_encode($userData);

            $this->userLastDataDao->save($userLastData);
        }

        $messageList = $this->findUnreadMessages($userId, $params['unreadMessageList'], $params['lastMessageTimestamp']);
        if (!empty($messageList))
        {
            $conversations = array();
            $notViewedConversations = 0;
            foreach($messageList as $message)
            {
                if (!in_array($message['convId'], $conversations))
                {
                    $conversations[] = $message['convId'];
                    if (!$message['conversationViewed'])
                    {
                        $notViewedConversations++;
                    }
                }
            }
            $result['messageList'] = $messageList;
            $result['newMessageCount'] = array('all'=>count($conversations), 'new'=>(int)$notViewedConversations);
        }
//        else
//        {
//            $result['messageList'] = '';
//            $result['newMessageCount'] = array('all'=>0, 'new'=>0);
//        }

        $data = json_decode($userLastData->data, true);

        
        if ($params['userOnlineCount'] === 0 || $data['userOnlineCount'] != $params['userOnlineCount'])
        {
            $result['userOnlineCount'] = $data['userOnlineCount'];
            $result['userList'] = $data['userList'];
        }

        if ($data['conversationsCount'] != $params['conversationsCount'])
        {
            $result['conversationsCount'] = $data['conversationsCount'];
            $result['convList'] = $data['convList'];
        }

        
        if (!empty($data['messageList']))
        {
            foreach($data['messageList'] as $id => $message)
            {
                if (in_array($message['id'], $params['unreadMessageList']))
                {
                    unset($data['messageList'][$id]);
                }
            }
            $result['messageList'] = $data['messageList'];
        }

//        $result['newMessageCount'] = $data['newMessageCount'];

        return $result;
    }

    public function getActiveModeList()
    {
        $event = new PEEP_Event('plugin.mailbox.get_active_modes');
        PEEP::getEventManager()->trigger($event);

        $activeModes = $event->getData();

        if (empty($activeModes))
        {
            $activeModes = json_decode( PEEP::getConfig()->getValue('mailbox', 'active_modes') );
        }

        return $activeModes;
    }

    public function getLastMessageTimestamp( $conversationId )
    {
        $message = $this->messageDao->findLastMessage($conversationId);

        return (!empty($message)) ? (int)$message->timeStamp : 0;
    }

    public function getLastMessageTimestampByUserIdList( $userIdList )
    {
        $result = array();
        $userId = PEEP::getUser()->getId();

        $messageList = $this->messageDao->findLastMessageByConversationIdListAndUserIdList( $userId, $userIdList );

        foreach($messageList as $message)
        {
            if ($message['recipientId'] == $userId)
            {
                $opponentId = $message['senderId'];
            }

            if ($message['senderId'] == $userId)
            {
                $opponentId = $message['recipientId'];
            }

            if (isset($result[$opponentId]))
            {
                if ( $result[$opponentId] < (int)$message['timeStamp'] )
                {
                    $result[$opponentId] = (int)$message['timeStamp'];
                }
            }
            else
            {
                $result[$opponentId] = (int)$message['timeStamp'];
            }
        }

        return $result;
    }

    public function getUserSettingsForm()
    {
        $form = new Form('im_user_settings_form');

        $findContact = new MAILBOX_CLASS_SearchField('im_find_contact');
        $findContact->setHasInvitation(true);
        $findContact->setInvitation(PEEP::getLanguage()->text('mailbox', 'find_contact'));
        $form->addElement($findContact);

        $userIdHidden = new HiddenField('user_id');
        $form->addElement($userIdHidden);


        return $form;
    }

    public function checkPermissions()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            return "You need to sign in";
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            return "Ajax request required";
        }

        return false;
    }

    public function getUserInfo( $opponentId, $userWithCorrespondenceIdList = null, $friendIdList = null )
    {
        $userId = PEEP::getUser()->getId();


        $profileUrl = BOL_UserService::getInstance()->getUserUrl($opponentId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);
        if ( empty($avatarUrl) )
        {
            $avatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }

        $isFriend = false;
        if ($friendIdList === null)
        {
            $friendIdList = array();
            $friendship = PEEP::getEventManager()->call('plugin.friends.check_friendship', array('userId' => $userId, 'friendId' => $opponentId));
            if ( !empty($friendship) && $friendship->getStatus() == 'active' )
            {
                $friendIdList[] = $opponentId;
            }
        }

        if (in_array($opponentId, $friendIdList))
        {
            $isFriend = true;
        }

        $wasCorrespondence = false;
        if ($userWithCorrespondenceIdList === null)
        {
            $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
            $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
        }
        else
        {
            $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($opponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($opponentId) : $profileDisplayname;
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($opponentId));
        $shortUserDataByUserIdList = $this->getFields(array($opponentId));

        $info = array(
            'opponentId' => (int)$opponentId,
            'displayName' => $profileDisplayname,
            'avatarUrl' => $avatarUrl,
            'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
            'profileUrl' => $profileUrl,
            'isFriend' => $isFriend,
            'status' => $conversationService->getUserStatus($opponentId),
            'lastMessageTimestamp' => $this->getLastMessageTimestamp($conversationId),
            'convId' => $conversationId, //here it is a chat conversation id
            'wasCorrespondence' => $wasCorrespondence,
            'shortUserData' => !empty($shortUserDataByUserIdList[$opponentId]) ? $shortUserDataByUserIdList[$opponentId] : $profileDisplayname
        );

        $activeModes = $this->getActiveModeList();
        if (in_array('chat', $activeModes))
        {
            $url = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            $info['url'] = $url;
            $info['canInvite'] = $this->getInviteToChatPrivacySettings($userId, $opponentId);

            if ( !$info['canInvite'] )
            {
                $info['wasBlocked'] = true;
            }
        }
        else
        {
            $url = PEEP::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
            $info['url'] = $url;
        }

        if ( BOL_UserService::getInstance()->isBlocked($opponentId) )
        {
            $info['wasBlocked'] = true;
        }

        return $info;
    }

    private function isBlockedByUserIdList($userId, $userIdList)
    {
        $userIdListString = PEEP::getDbo()->mergeInClause($userIdList);
        $sql = "SELECT `userId` FROM `".BOL_UserBlockDao::getInstance()->getTableName()."` WHERE `blockedUserId` = :userId AND `userId` IN ( {$userIdListString} )";

        return PEEP::getDbo()->queryForList($sql, array('userId'=>$userId));
    }


    public function getFields( $userIdList )
    {
        $fields = array();

        foreach($userIdList as $userId)
        {
            $fields[$userId] = '';
        }

        $qs = array();

        $qs[] = 'username';

        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
        $qs[] = $questionName;

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');
        if ($qLocation)
        {
            if ( $qLocation->onView )
            {
                $qs[] = 'googlemap_location';
            }
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach($questionList as $userId => $question)
        {
            $userFields = array();

            $fields[$userId] = isset($question[$questionName]) ? "<b>".$question[$questionName]."</b>" : "<b>".$question['username']."</b>";

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $userFields['sex'] = substr($sexValue, 0, -2);
                    $fields[$userId] .= "<br/>".$userFields['sex'];
                }
            }

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $userFields['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                $fields[$userId] .= "<br/>".$userFields['age'];
            }


            if (!empty($question['googlemap_location']))
            {
                $userFields['googlemap_location'] = $question['googlemap_location']['address'];
                $fields[$userId] .= "<br/>".$userFields['googlemap_location'];
            }
        }

        return $fields;
    }

    public function getUserInfoForUserIdList( $userIdList, $userWithCorrespondenceIdList = array(), $friendIdList = array() )
    {
        if (empty($userIdList))
        {
            return array();
        }
        $activeModes = $this->getActiveModeList();

        $userInfoList = array();
        $userId = PEEP::getUser()->getId();

        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        $conversationIdByUserIdList = $this->getChatConversationIdWithUserByIdList($userId, $userIdList);
        $friendIdList = PEEP::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        $shortUserDataByUserIdList = $this->getFields($userIdList);

        if (empty($friendIdList))
        {
            $friendIdList = array();
        }

        $lastMessageTimestampByUserIdList = $this->getLastMessageTimestampByUserIdList($userIdList);

        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        foreach ($userIdList as $opponentId)
        {
            $wasCorrespondence = false;
            if ($userWithCorrespondenceIdList === null)
            {
                $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }
            else
            {
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }

            $conversationId = array_key_exists($opponentId, $conversationIdByUserIdList) ? $conversationIdByUserIdList[$opponentId] : 0;

            $info = array(
                'opponentId' => (int)$opponentId,
                'displayName' => empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'],
                'avatarUrl' => $avatarData[$opponentId]['src'],
                'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
                'profileUrl' => $avatarData[$opponentId]['url'],
                'isFriend' => in_array($opponentId, $friendIdList),
                'status' => $onlineStatusByUserIdList[$opponentId],
                'lastMessageTimestamp' => array_key_exists($opponentId, $lastMessageTimestampByUserIdList) ? $lastMessageTimestampByUserIdList[$opponentId] : 0,
                'convId' => (int)$conversationId, //here it is a chat conversation id
                'wasCorrespondence' => $wasCorrespondence,
                'shortUserData' => $shortUserDataByUserIdList[$opponentId]
            );

            if (in_array('chat', $activeModes))
            {
                $url = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
                $info['url'] = $url;
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }
            else
            {
                $url = PEEP::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
                $info['url'] = $url;
            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getUserInfoForUserIdListForApi( $userIdList, $userWithCorrespondenceIdList = array(), $friendIdList = array() )
    {
        if (empty($userIdList))
        {
            return array();
        }
        $activeModes = $this->getActiveModeList();

        $userInfoList = array();
        $userId = PEEP::getUser()->getId();

        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, false, true, true);
        $conversationIdByUserIdList = $this->getChatConversationIdWithUserByIdList($userId, $userIdList);
        $friendIdList = PEEP::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        $shortUserDataByUserIdList = $this->getFields($userIdList);

        if (empty($friendIdList))
        {
            $friendIdList = array();
        }

        $lastMessageTimestampByUserIdList = $this->getLastMessageTimestampByUserIdList($userIdList);

        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        foreach ($userIdList as $opponentId)
        {
            $wasCorrespondence = false;
            if ($userWithCorrespondenceIdList === null)
            {
                $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }
            else
            {
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }

            $conversationId = array_key_exists($opponentId, $conversationIdByUserIdList) ? $conversationIdByUserIdList[$opponentId] : 0;

            $info = array(
                'opponentId' => (int)$opponentId,
                'displayName' => empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'],
                'avatarUrl' => $avatarData[$opponentId]['src'],
                'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
                'profileUrl' => '',
                'isFriend' => in_array($opponentId, $friendIdList),
                'status' => $onlineStatusByUserIdList[$opponentId],
                'lastMessageTimestamp' => array_key_exists($opponentId, $lastMessageTimestampByUserIdList) ? $lastMessageTimestampByUserIdList[$opponentId] : 0,
                'convId' => (int)$conversationId, //here it is a chat conversation id
                'wasCorrespondence' => $wasCorrespondence,
                'shortUserData' => $shortUserDataByUserIdList[$opponentId]
            );

            if (in_array('chat', $activeModes))
            {
//                $url = PEEP::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
//                $info['url'] = $url;
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }
//            else
//            {
//                $url = PEEP::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
//                $info['url'] = $url;
//            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getUserOnlineInfoForUserIdList( $userIdList, $userWithCorrespondenceIdList = null, $friendIdList = null )
    {
        if (empty($userIdList))
        {
            return array();
        }

        $activeModes = $this->getActiveModeList();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $userInfoList = array();
        $userId = PEEP::getUser()->getId();

        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        foreach ($userIdList as $opponentId)
        {
            $info = array(
                'status' => $onlineStatusByUserIdList[$opponentId],
            );

            if (in_array('chat', $activeModes))
            {
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getInviteToChatPrivacySettings($userId, $opponentId)
    {
        $eventParams = array(
            'action' => 'mailbox_invite_to_chat',
            'ownerId' => $opponentId,
            'viewerId' => $userId
        );

        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }

        return true;
    }

    public function getViewPresenceOnSitePrivacySettings($userId, $opponentId)
    {
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $opponentId,
            'viewerId' => $userId
        );

        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {

            return false;
        }

        return true;
    }

    private function getPrivacySettingsForUserIdList( $actionName, $userId, $userIdList)
    {
        $eventParams = array(
            'action' => $actionName,
            'ownerIdList' => $userIdList,
            'viewerId' => $userId
        );

        $permissions = PEEP::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        $result = array();

        foreach($userIdList as $opponentId)
        {
            if ( isset($permissions[$opponentId]['blocked']) && $permissions[$opponentId]['blocked'] == true )
            {
                $result[$opponentId] = false;
            }
            else
            {
                $result[$opponentId] = true;
            }
        }

        return $result;
    }

    public function getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList)
    {
        return $this->getPrivacySettingsForUserIdList('mailbox_invite_to_chat', $userId, $userIdList);
    }

    public function getViewPresenceOnSitePrivacySettingsForUserIdList($userId, $userIdList)
    {
        return $this->getPrivacySettingsForUserIdList('base_view_my_presence_on_site', $userId, $userIdList);
    }

    public function getUserListWithCorrespondence()
    {
        $userId = PEEP::getUser()->getId();

        $userIdList = $this->messageDao->findUserListWithCorrespondence($userId);

        return $userIdList;
    }

    public function getUserListWithCorrespondenceAlt($friendIdList)
    {
        $userId = PEEP::getUser()->getId();

        $userIdList = $this->messageDao->findUserListWithCorrespondenceAlt($userId, $friendIdList);

        return $userIdList;
    }

    public function countConversationListByUserId($userId)
    {
        $activeModes = $this->getActiveModeList();

        return (int)$this->conversationDao->countConversationListByUserId($userId, $activeModes);
    }

    public function getConversationListByUserId($userId, $from = 0, $count = 50){
        $data = array();

        $activeModes = $this->getActiveModeList();
        $conversationItemList = $this->conversationDao->findConversationItemListByUserId($userId, $activeModes, $from, $count);

        foreach($conversationItemList as $i => $conversation)
        {
                $conversationItemList[$i]['timeStamp'] = (int)$conversation['initiatorMessageTimestamp'];
                $conversationItemList[$i]['lastMessageSenderId'] = $conversation['initiatorMessageSenderId'];
                $conversationItemList[$i]['isSystem'] = $conversation['initiatorMessageIsSystem'];
                $conversationItemList[$i]['text'] = $conversation['initiatorText'];

                $conversationItemList[$i]['lastMessageId'] = $conversation['initiatorLastMessageId'];
                $conversationItemList[$i]['recipientRead'] = $conversation['initiatorRecipientRead'];
                $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['initiatorMessageRecipientId'];
                $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['initiatorMessageWasAuthorized'];
        }

        $data = $this->getConversationItemByConversationIdListForApi( $conversationItemList );

        return $data;
    }

//    public function sortConversationList($a, $b)
//    {
//        return $a['timeStamp'] < $b['timeStamp'] ? 1 : -1;
//    }

    public function getConversationDeletedTimestamp($conversationId)
    {
        $deletedTimestamp = 0;
        $conversation = $this->getConversation($conversationId);
        if ($conversation->initiatorId == PEEP::getUser()->getId())
        {
            $deletedTimestamp = $conversation->initiatorDeletedTimestamp;
        }
        else
        {
            $deletedTimestamp = $conversation->interlocutorDeletedTimestamp;
        }

        return $deletedTimestamp;
    }

    public function getNewConsoleConversationCount( $userId, $messageList )
    {
        $convList = array();
        foreach ($messageList as $messageData)
        {
            if (!in_array($messageData['convId'], $convList))
            {
                $convList[] = $messageData['convId'];
            }
        }
        return $this->conversationDao->getNewConversationCountForConsole($userId, $convList);
    }

    public function getViewedConversationCountForConsole( $userId, $messageList )
    {
        $convList = array();
        foreach ($messageList as $messageData)
        {
            if (!in_array($messageData['convId'], $convList))
            {
                $convList[] = $messageData['convId'];
            }
        }

        return $this->conversationDao->getViewedConversationCountForConsole($userId, $convList);
    }

    public function getConsoleConversationList( $userId, $first, $count, $lastPingTime, $ignoreList = array() )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        $activeModes = $this->getActiveModeList();

        return $this->conversationDao->getConsoleConversationList($activeModes, $userId, $first, $count, $lastPingTime, $ignoreList);
    }

    public function getMarkedUnreadConversationList( $userId, $ignoreList = array() )
    {
        $list = $this->conversationDao->getMarkedUnreadConversationList( $userId, $ignoreList, $this->getActiveModeList() );
        foreach($list as $id => $value)
        {
            $list[$id] = (int)$value;
        }

        return $list;
    }

    public function getInboxConversationList( $userId, $first, $count )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            throw new InvalidArgumentException("Not numeric params were provided! Numbers are expected!");
        }

        return $this->conversationDao->getInboxConversationList($userId, $first, $count);
    }

    public function countUnreadMessagesForConversation($convId, $userId)
    {
        return (int)$this->messageDao->countUnreadMessagesForConversation($convId, $userId);
    }

    public function countUnreadMessagesForConversationList($conversationIdList, $userId)
    {
        if (count($conversationIdList) == 0)
        {
            return array();
        }

        $list = $this->messageDao->countUnreadMessagesForConversationList($conversationIdList, $userId);

        $result = array();
        foreach($list as $item)
        {
            $result[$item['conversationId']] = $item['count'];
        }

        return $result;
    }

    public function checkUser($userId, $conversationOpponentId)
    {
        $language = PEEP::getLanguage();
        $user = BOL_UserService::getInstance()->findUserById($conversationOpponentId);
        $result = array();

        if (empty($user))
        {
            $result['isSuspended'] = true;
            $result['suspendReasonMessage'] = $language->text('mailbox', 'user_is_deleted');//TODO add lang
        }
        else
        {
            $suspendReason = '';
            $isDeleted = false;

            $isSuspended = BOL_UserService::getInstance()->isSuspended($conversationOpponentId);
            if ($isSuspended)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_suspended');
            }

            $isApproved = true;
            if ( PEEP::getConfig()->getValue('base', 'mandatory_user_approve') )
            {
                $isApproved = BOL_UserService::getInstance()->isApproved($conversationOpponentId);
            }
            if (!$isApproved)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_not_approved');
            }

            $emailVerified = true;
            if ( PEEP::getConfig()->getValue('base', 'confirm_email') )
            {
                $emailVerified = $user->emailVerify;
            }
            if (!$emailVerified)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_not_verified');
            }

            $isAuthorizedReadMessage = true;

            $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, $conversationOpponentId);
            if ($isBlocked)
            {
                $suspendReasonMessage = $language->text('base', 'user_block_message');
                $suspendReason = 'isBlocked';
            }

            $result['isSuspended'] = $isSuspended || !$isApproved || !$emailVerified || !$isAuthorizedReadMessage || $isBlocked;

            if ($result['isSuspended'])
            {
                $result['suspendReasonMessage'] = $suspendReasonMessage;
                $result['suspendReason'] = $suspendReason;
            }
        }

        return $result;
    }

    public function getConversationLength($conversationId)
    {
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);

        return (int)$this->messageDao->getConversationLength($conversationId, $deletedTimestamp);
    }

    /**
     * Application event methods
     */
    public function getUnreadMessageCount( $userId, $ignoreList = array(), $time = null, $activeModes = array() )
    {
        $ignoreList = empty($ignoreList) ? array() : (array)$ignoreList;
        $time = $time == null ? time() : (int)$time;
        $activeModes = empty($activeModes) ? $this->getActiveModeList() : $activeModes;
        $messageList = $this->messageDao->findUnreadMessages($userId, $ignoreList, $time, $activeModes);

        $winkList = array();
        if (PEEP::getPluginManager()->isPluginActive('winks'))
        {
            $winks = WINKS_BOL_Service::getInstance()->findWinkList( $userId, 0, 9999 );
            foreach ($winks as $wink)
            {
                if ($wink->userId == $userId)
                {
                    continue;
                }

                if ($wink->status == 'wait')
                {
                    $winkList[] = $wink->userId;
                }
            }
        }

        return count($messageList) + count($winkList);
    }

    public function getConversationRead(MAILBOX_BOL_Conversation $conversation, $userId)
    {
        $conversationRead = 0;
        switch ( $userId )
        {
            case $conversation->initiatorId:
                if ( (int) $conversation->read & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                {
                    $conversationRead = 1;
                }

                break;

            case $conversation->interlocutorId:

                if ( (int) $conversation->read & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                {
                    $conversationRead = 1;
                }

                break;
        }

        return $conversationRead;
    }

    public function getShortUserInfo( $opponentId )
    {
        $conversationId = $this->getChatConversationIdWithUserById(PEEP::getUser()->getId(), $opponentId);
        if (!empty($conversationId))
        {
            $conversation = $this->getConversation($conversationId);

            $conversationRead = $this->getConversationRead($conversation, PEEP::getUser()->getId());
            $lastMessageTimestamp = $this->getLastMessageTimestamp($conversationId);
        }
        else
        {
            $conversationRead = 1;
            $lastMessageTimestamp = 0;
        }

        return array(
            'userId'=>$opponentId,
            'conversationRead'=>$conversationRead,
            'timeStamp'=>$lastMessageTimestamp
        );
    }

    public function getChatUserList( $userId, $from = 0, $count = 10 )
    {
        $conversationList = $this->getConversationListByUserId(PEEP::getUser()->getId(), $from, $count);

        return $conversationList;

//        $data = array();
//        $list = array();
//
//        $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
//
//        if (PEEP::getPluginManager()->isPluginActive('winks'))
//        {
//            $winks = WINKS_BOL_Service::getInstance()->findWinkList( $userId, $from, $count );
//            foreach ($winks as $wink)
//            {
//                if ($wink->userId == $userId)
//                {
//                    continue;
//                }
//
//                if ($wink->status == 'wait')
//                {
//                    if (!in_array($wink->userId, $userWithCorrespondenceIdList))
//                    {
//                        $userWithCorrespondenceIdList[] = $wink->userId;
//                    }
//                }
//            }
//        }
//
//        if (empty($userWithCorrespondenceIdList))
//        {
//            return array();
//        }
//        foreach($userWithCorrespondenceIdList as $id)
//        {
//            $data[$id] = $this->getShortUserInfo($id);
//        }
//
//        $idList = array();
//        $viewedMap = array();
//        $timeMap = array();
//        $timeStamps = array();
//        foreach ( $data as $item )
//        {
//            $idList[] = $item["userId"];
//            $viewedMap[$item["userId"]] = $item["conversationRead"];
//            $timeMap[$item["userId"]] = $item["timeStamp"] > 0 ? UTIL_DateTime::formatDate($item["timeStamp"]) : "";
//            $timeStamps[$item["userId"]] = $item["timeStamp"] > 0 ? $item["timeStamp"] : 0;
//        }
//
//        $userService = BOL_UserService::getInstance();
//        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, false);
//        $onlineMap = BOL_UserService::getInstance()->findOnlineStatusForUserList($idList);
//
//        foreach ( $avatarList as $opponentId => $user )
//        {
//            $winkReceived = PEEP::getEventManager()->call('winks.isWinkSent', array('userId'=>$opponentId, 'partnerId'=>$userId));
//
//            $list[] = array(
//                "userId" => $opponentId,
//                "displayName" => !empty($user["title"]) ? $user["title"] : $userService->getUserName($opponentId),
//                "avatarUrl" => $user["src"],
//                "viewed" => $viewedMap[$opponentId],
//                "online" => $onlineMap[$opponentId],
//                "time" => $timeMap[$opponentId],
//                "lastMessageTimestamp" => $timeStamps[$opponentId],
//                'winkReceived' => (int)$winkReceived
//            );
//        }
//
//        return $list;
    }

    public function getChatNewMessages($userId, $opponentId, $lastMessageTimestamp)
    {
        $conversationId = $this->getChatConversationIdWithUserById($userId, $opponentId);

        if (!empty($conversationId))
        {
            $dtoList = $this->messageDao->findConversationMessagesByLastMessageTimestamp($conversationId, $lastMessageTimestamp);
            $list = array();
            foreach($dtoList as $dto)
            {
                $list[] = $this->getMessageDataForApi($dto);
            }
        }
        else
        {
            $list = array();
        }

        return $list;
    }


    public function getNewMessagesForConversation( $conversationId, $lastMessageTimestamp = null )
    {
        if ( ($conversation = $this->getConversation($conversationId)) === null )
        {
            return array();
        }

        if ( empty($lastMessageTimestamp) )
        {
            $lastMessageTimestamp = time();
        }

        $result = array();
        $messageList = $this->messageDao->findConversationMessagesByLastMessageTimestamp($conversation->id, $lastMessageTimestamp);

        foreach ( $messageList as $message )
        {
            $result[] = $this->getMessageDataForApi($message);
        }

        return $result;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageDataForApi( $message )
    {
        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $item = array();

        $item['convId'] = (int)$message->conversationId;
        $item['mode'] = $this->getConversationMode((int)$message->conversationId);
        $item['id'] = (int)$message->id;
        $item['date'] = date('Y-m-d', (int)$message->timeStamp);
        $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
        $item['timeStamp'] = (int)$message->timeStamp;

        $militaryTime = (bool) PEEP::getConfig()->getValue('base', 'military_time');
        $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
        $item['recipientId'] = (int)$message->recipientId;
        $item['senderId'] = (int)$message->senderId;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName((int)$message->senderId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName((int)$message->senderId) : $profileDisplayname;
        $item['displayName'] = $profileDisplayname;


        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl((int)$message->senderId);
        $profileAvatarUrl = empty($avatarUrl) ? $defaultAvatarUrl : $avatarUrl;
        $item['senderAvatarUrl'] = $profileAvatarUrl;

        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl((int)$message->recipientId);
        $profileAvatarUrl = empty($avatarUrl) ? $defaultAvatarUrl : $avatarUrl;
        $item['recipientAvatarUrl'] = $profileAvatarUrl;

        $item['isAuthor'] = (bool)((int)$message->senderId == PEEP::getUser()->getId());
        $item['recipientRead'] = (int)$message->recipientRead;
        $item['isSystem'] = (int)$message->isSystem;
        $item['attachments'] = array();

        $conversation = $this->getConversation($message->conversationId);
        if ( (int)$conversation->initiatorId == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation->interlocutorId == PEEP::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        if ($item['mode'] == 'mail')
        {
            $actionName = 'read_message';
        }

        if ($item['mode'] == 'chat')
        {
            $actionName = 'read_chat_message';
        }

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);

        $readMessageAuthorized = true;

        if ( (int)$message->senderId != PEEP::getUser()->getId() && !$message->wasAuthorized )
        {
            if ($status['status'] == BOL_AuthorizationService::STATUS_AVAILABLE)
            {
                if ($status['authorizedBy'] == 'usercredits')
                {
                    $action = USERCREDITS_BOL_CreditsService::getInstance()->findAction('mailbox', $actionName);
                    $actionPrice = USERCREDITS_BOL_CreditsService::getInstance()->findActionPriceForUser($action->id, PEEP::getUser()->getId());

                    if ($actionPrice->amount == 0 || $actionPrice->disabled)
                    {
                        $readMessageAuthorized = true;
                        $this->markMessageAuthorizedToRead($message->id);
                    }
                    else
                    {
                        $readMessageAuthorized = false;
                        $item['isSystem'] = 1;

                        $text = array(
                            'text'=>PEEP::getLanguage()->text('mailbox', 'api_read_the_message'),
                            'eventName'=>'authorizationPromoted',
                            'status'=>'available'
                        );
                    }
                }
                else
                {
                    $readMessageAuthorized = true;
                    $this->markMessageAuthorizedToRead($message->id);
                }
            }
            else if ($status['status'] == BOL_AuthorizationService::STATUS_PROMOTED)
            {
                $readMessageAuthorized = false;

                $item['isSystem'] = 1;
                $text = array(
                    'text'=>strip_tags($status['msg']),
                    'eventName'=>'authorizationPromoted',
                    'status'=>'promoted'
                );
            }
            else
            {
                $readMessageAuthorized = false;
                $text = PEEP::getLanguage()->text('mailbox', $actionName.'_permission_denied');
            }
        }

        $item['readMessageAuthorized'] = $readMessageAuthorized;

        if ($readMessageAuthorized)
        {
            if ($message->isSystem)
            {
                $eventParams = json_decode($message->text, true);
                $eventParams['params']['messageId'] = (int)$message->id;

                $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                PEEP::getEventManager()->trigger($event);

                $data = $event->getData();

                if (!empty($data))
                {
                    $text = $data;
                }
                else
                {
                    $text = array(
                        'eventName' => $eventParams['eventName'],
                        'text' => PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']))
                    );
                }
            }
            else
            {
                $text = $this->splitLongMessages($message->text);
            }

            $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
            if (!empty($attachments))
            {
                foreach($attachments as $attachment)
                {
                    $ext = UTIL_File::getExtension($attachment->fileName);
                    $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                    $attItem = array();
                    $attItem['id'] = $attachment->id;
                    $attItem['messageId'] = $attachment->messageId;
                    $attItem['downloadUrl'] = PEEP::getStorage()->getFileUrl($attachmentPath);
                    $attItem['fileName'] = $attachment->fileName;
                    $attItem['fileSize'] = $attachment->fileSize;
                    $attItem['type'] = $this->getAttachmentType($attachment);

                    $item['attachments'][] = $attItem;
                }
            }
        }

        $item['text'] = $text;

        return $item;
    }

    public function getMessagesForApi($userId, $conversationId)
    {
        $list = array();
        $length = 0;

        if (!empty($conversationId))
        {
            $count = 16;
            $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);

            $dtoList = $this->messageDao->findListByConversationId($conversationId, $count, $deletedTimestamp);

            foreach($dtoList as $message)
            {
                $list[] = $this->getMessageDataForApi($message);
            }

            $length = $this->getConversationLength($conversationId);
        }

        return array('list'=>$list, 'length'=>$length);
    }

    public function findUnreadMessagesForApi( $userId, $ignoreList, $timeStamp = null )
    {
        $list = array();

        $messages = $this->messageDao->findUnreadMessages($userId, $ignoreList, $timeStamp, $this->getActiveModeList());

        foreach($messages as $id=>$message)
        {
            $list[] = $this->getMessageDataForApi($message);
        }

        return $list;
    }
    /**
     *
     */

    public function getConversationsWithAttachmentFromConversationList($conversationIdList)
    {
        if (empty($conversationIdList))
        {
            return array();
        }

        $list = $this->attachmentDao->findConversationsWithAttachmentFromConversationList($conversationIdList);

        $result = array();
        foreach($conversationIdList as $conversationId)
        {
            if (in_array($conversationId, $list))
            {
                $result[$conversationId] = true;
            }
            else
            {
                $result[$conversationId] = false;
            }
        }

        return $result;
    }

    public function checkUserSendMessageInterval($userId)
    {
        $send_message_interval = (int)PEEP::getConfig()->getValue('mailbox', 'send_message_interval');
        $conversation = $this->conversationDao->findUserLastConversation($userId);
        if ($conversation != null)
        {
            if (time()-$conversation->createStamp < $send_message_interval)
            {
                return false;
            }
        }

        return true;
    }
    
    public function deleteAttachmentFiles()  // this method has calling from cron
    {
        $attachDtoList = $this->attachmentDao->getAttachmentForDelete();        
     
        foreach ($attachDtoList as $attachDto)
        {   /* @var $attachDto MAILBOX_BOL_Attachment */
            $ext = UTIL_File::getExtension($attachDto->fileName);
            $attachmentPath = $this->getAttachmentFilePath($attachDto->id, $attachDto->hash, $ext, $attachDto->fileName);
              
            try
            {
                PEEP::getStorage()->removeFile($attachmentPath);
                $this->attachmentDao->deleteById($attachDto->id);
            }
            catch (Exception $ex)
            {
            }
            
            
        }        
        
    }
}