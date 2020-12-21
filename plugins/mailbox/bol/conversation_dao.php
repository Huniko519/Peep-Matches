<?php

class MAILBOX_BOL_ConversationDao extends PEEP_BaseDao
{
    const READ_NONE = 0;
    const READ_INITIATOR = 1;
    const READ_INTERLOCUTOR = 2;
    const READ_ALL = 3;

    const DELETED_NONE = 0;
    const DELETED_INITIATOR = 1;
    const DELETED_INTERLOCUTOR = 2;
    const DELETED_ALL = 3;

    const VIEW_NONE = 0;
    const VIEW_INITIATOR = 1;
    const VIEW_INTERLOCUTOR = 2;
    const VIEW_ALL = 3;

    const CACHE_LIFE_TIME = 86400;

    const CACHE_TAG_USER_CONVERSATION_COUNT = 'mailbox_conversation_count_user_id_';
    const CACHE_TAG_USER_NEW_CONVERSATION_COUNT = 'mailbox_new_conversation_count_user_id_';

    const CHAT_CONVERSATION_SUBJECT = 'mailbox_chat_conversation';
    const WINK_CONVERSATION_SUBJECT = 'Wink';

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var MAILBOX_BOL_ConversationDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_ConversationDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see MAILBOX_BOL_ConversationDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'MAILBOX_BOL_Conversation';
    }

    /**
     * @see MAILBOX_BOL_ConversationDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'mailbox_conversation';
    }

    /**
     * Returns conversations list by $userId
     *
     * @param int $userId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function getConversationListByUserId( $userId, $first, $count )
    {
        $sql = " SELECT `conv`.* FROM `" . $this->getTableName() . "` AS `conv`
            WHERE `conv`.`initiatorId` = :userId LIMIT :start, :count";

        return $this->dbo->queryForList($sql, array('userId' => $userId, 'start' => $first, 'count' => $count));
    }

    public function getAffectedRows()
    {
        return $this->dbo->getAffectedRows();
    }

    public function findConversationList( $initiatorId, $interlocutorId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('initiatorId', $initiatorId);
        $ex->andFieldEqual('interlocutorId', $interlocutorId);
        $ex->setOrder('id');

        return $this->findListByExample($ex);
    }
    /**
    *
    * @param array $userId
    * @return array
    */
    public function getNewConversationListForConsoleNotificationMailer( $userIdList )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $userList = $this->dbo->mergeInClause($userIdList);

        $sql = " SELECT `mess`.`id` as messageId, `mess`.*, `conv`.* FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON (`last_m`.`conversationId` = `conv`.`id`)

            	 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `last_m`.`initiatorMessageId` = `mess`.id AND ( `last_m`.`initiatorMessageId` > `last_m`.interlocutorMessageId )
                    OR `last_m`.`interlocutorMessageId` = `mess`.id AND ( `last_m`.`initiatorMessageId` < `last_m`.interlocutorMessageId ) )

			     WHERE  `conv`.`notificationSent` = 0 AND ( ( `conv`.`initiatorId` IN ( $userList ) AND `last_m`.`interlocutorMessageId` > 0 AND `conv`.`deleted` != " . self::DELETED_INITIATOR . " AND NOT `conv`.`read` & " . (self::READ_INITIATOR) . "  AND NOT `conv`.`viewed` &  " . (self::VIEW_INITIATOR) . " )
					 	OR ( `conv`.`interlocutorId` IN ( $userList ) AND `conv`.`deleted` != " . self::DELETED_INTERLOCUTOR . "  AND  NOT `conv`.`read` & " . (self::READ_INTERLOCUTOR) . " AND NOT `conv`.`viewed` &  " . (self::VIEW_INTERLOCUTOR) . " ) ) 
        ";

        $conversationList = $this->dbo->queryForList($sql);
        $resultList = array();

        foreach ( $conversationList as $conversation )
        {
            $userId = $conversation['recipientId'];

            if ($conversation['wasAuthorized'] == 0)
            {
                $actionName = 'read_message';
                if ($conversation['subject'] == 'mailbox_chat_conversation')
                {
                    $actionName = 'read_chat_message';
                }

                if (!PEEP::getAuthorization()->isUserAuthorized($userId, 'mailbox', $actionName))
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
                    $conversation['text'] = $status['msg'];
                }
            }
            else
            {
                if ($conversation['isSystem'] == 1)
                {
                    $eventParams = json_decode($conversation['text'], true);
                    $eventParams['params']['messageId'] = $conversation['messageId'];
                    $eventParams['params']['getPreview'] = true;

                    $event = new PEEP_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                    PEEP::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if (!empty($data))
                    {
                        $conversation['text'] = $data;
                    }
                    else
                    {
                        $conversation['text'] = PEEP::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']));
                    }
                }
            }

            $resultList[$userId][] = $conversation;
        }

        return $resultList;
    }

    public function getMailsConversationIdList($userId, $first, $count)
    {
        $sql = " SELECT `conv`.`id`, MAX(mess.timeStamp) as `messageTimestamp`  FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE `conv`.`subject` <> '".self::CHAT_CONVERSATION_SUBJECT."' AND ( ( `conv`.`initiatorId` = :user AND `conv`.`deleted` != " . self::DELETED_INITIATOR . " AND ( `mess`.`id` = `last_m`.`initiatorMessageId` OR `mess`.`id` = `last_m`.`interlocutorMessageId`) )
					 	OR ( `conv`.`interlocutorId` = :user AND `conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " AND ( `mess`.`id` = `last_m`.`initiatorMessageId` OR `mess`.`id` = `last_m`.`interlocutorMessageId`) ) )

                 GROUP BY `conv`.`id`

				 ORDER BY `messageTimestamp` DESC

				 LIMIT :first, :count ";

        return $this->dbo->queryForList($sql, array('user' => $userId, 'first' => $first, 'count' => $count));
    }

    public function getChatsConversationIdList($userId, $first, $count)
    {
        $sql = " SELECT `conv`.`id`, MAX(mess.timeStamp) as `messageTimestamp`  FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE `conv`.`subject` = '".self::CHAT_CONVERSATION_SUBJECT."' AND (( `conv`.`initiatorId` = :user AND `conv`.`deleted` != " . self::DELETED_INITIATOR . "  )
					 	OR ( `conv`.`interlocutorId` = :user AND `conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " ))

                 GROUP BY `conv`.`id`

				 ORDER BY `messageTimestamp` DESC

				 LIMIT :first, :count ";

        return $this->dbo->queryForList($sql, array('user' => $userId, 'first' => $first, 'count' => $count));
    }

    public function getConversationItem( $convId )
    {
        $sql = " SELECT `conv`.`id`, `conv`.`initiatorId`, `conv`.`interlocutorId`, `conv`.`subject`, `conv`.`read`, `conv`.`viewed`, `mess`.`id` as 'lastMessageId', `mess`.`text`, `mess`.`recipientRead`, `mess`.`timeStamp`, `mess`.`isSystem`, `mess`.`senderId` as `lastMessageSenderId`,  `mess`.`recipientId` as `lastMessageRecipientId`, `mess`.`wasAuthorized` as `lastMessageWasAuthorized`, `last_m`.`initiatorMessageId`, `last_m`.`interlocutorMessageId`  FROM `" . $this->getTableName() . "` AS `conv`

				 LEFT JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `last_m`.`initiatorMessageId` = `mess`.id OR `last_m`.`interlocutorMessageId` = `mess`.id )

				 WHERE `conv`.`id` = :convId
				 ORDER BY `mess`.`timeStamp` DESC, `mess`.`id` DESC
                 LIMIT 1";

        return $this->dbo->queryForRow($sql, array('convId' => $convId));
    }

    public function findChatConversationIdWithUserById($userId, $opponentId)
    {
        $sql = "SELECT `id` FROM `".$this->getTableName()."` WHERE `subject`=:subject AND ( `initiatorId`=:userId AND `interlocutorId`=:opponentId OR `initiatorId`=:opponentId AND `interlocutorId`=:userId )";

        return (int)$this->dbo->queryForColumn($sql, array('subject'=>self::CHAT_CONVERSATION_SUBJECT, 'userId'=>$userId, 'opponentId'=>$opponentId));
    }

    public function findChatConversationIdWithUserByIdList($userId, $userIdList)
    {
        $userIdListString = $this->dbo->mergeInClause($userIdList);

        $sql = "SELECT `id`, IF (`initiatorId` IN ( {$userIdListString} ), initiatorId, interlocutorId) AS opponentId FROM `".$this->getTableName()."` WHERE `subject`=:subject AND ( `initiatorId`=:userId AND `interlocutorId` IN ( {$userIdListString} ) OR `initiatorId` IN ( {$userIdListString} ) AND `interlocutorId`=:userId )";

        return $this->dbo->queryForList($sql, array('subject'=>self::CHAT_CONVERSATION_SUBJECT, 'userId'=>$userId));
    }

    public function findWinkConversationIdWithUserById($userId, $opponentId)
    {
        $sql = "SELECT `id` FROM `".$this->getTableName()."` WHERE `subject`=:subject AND ( `initiatorId`=:userId AND `interlocutorId`=:opponentId OR `initiatorId`=:opponentId AND `interlocutorId`=:userId )";

        return (int)$this->dbo->queryForColumn($sql, array('subject'=>self::WINK_CONVERSATION_SUBJECT, 'userId'=>$userId, 'opponentId'=>$opponentId));
    }

    public function findConversationListByUserId($userId, $activeModes)
    {
        if (in_array('chat', $activeModes) && in_array('mail', $activeModes))
        {
            $condition = "1 ";
        }
        else
        {
            $condition = "1 ";

            if (in_array('chat', $activeModes))
            {
                $condition .= "AND `conv`.`subject` = '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }

            if (in_array('mail', $activeModes))
            {
                $condition .= "AND `conv`.`subject` <> '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }
        }

        $sql = " SELECT `conv`.`id`, MAX(mess.timeStamp) as `messageTimestamp`  FROM `" . $this->getTableName() . "` AS `conv`

				 LEFT JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE {$condition} AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `mess`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `mess`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) )) AND `last_m`.`id` IS NOT NULL

                 GROUP BY `conv`.`id`

				 ORDER BY `messageTimestamp` DESC";

        return $this->dbo->queryForColumnList($sql, array('user' => $userId));
    }

    public function findConversationItemListByUserId($userId, $activeModes, $from = 0, $count = 50)
    {
        if (in_array('chat', $activeModes) && in_array('mail', $activeModes))
        {
            $condition = "1 ";
        }
        else
        {
            $condition = "1 ";

            if (in_array('chat', $activeModes))
            {
                $condition .= "AND `conv`.`subject` = '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }

            if (in_array('mail', $activeModes))
            {
                $condition .= "AND `conv`.`subject` <> '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }
        }
//
//        $sql = " SELECT `conv`.`id`,
//                        `conv`.`initiatorId`,
//                        `conv`.`interlocutorId`,
//                        `conv`.`subject`,
//                        `conv`.`read`,
//                        `conv`.`viewed`,
//
//                        `last_m`.`initiatorMessageId`,
//                        `initiatorMessage`.`id` as initiatorLastMessageId,
//                        `initiatorMessage`.`text` as initiatorText,
//                        `initiatorMessage`.`recipientRead` as initiatorRecipientRead,
//                        `initiatorMessage`.`isSystem` as initiatorMessageIsSystem,
//                        `initiatorMessage`.`senderId` as `initiatorMessageSenderId`,
//                        `initiatorMessage`.`recipientId` as `initiatorMessageRecipientId`,
//                        `initiatorMessage`.`wasAuthorized` as `initiatorMessageWasAuthorized`,
//                        `initiatorMessage`.`timeStamp` as `initiatorMessageTimestamp`,
//
//                        `last_m`.`interlocutorMessageId`,
//                        `interlocutorMessage`.`id` as interlocutorLastMessageId,
//                        `interlocutorMessage`.`text` as interlocutorText,
//                        `interlocutorMessage`.`recipientRead` as interlocutorRecipientRead,
//                        `interlocutorMessage`.`isSystem` as interlocutorMessageIsSystem,
//                        `interlocutorMessage`.`senderId` as `interlocutorMessageSenderId`,
//                        `interlocutorMessage`.`recipientId` as `interlocutorMessageRecipientId`,
//                        `interlocutorMessage`.`wasAuthorized` as `interlocutorMessageWasAuthorized`,
//                        `interlocutorMessage`.`timeStamp` as `interlocutorMessageTimestamp`
//
//                 FROM `" . $this->getTableName() . "` AS `conv`
//
//				 LEFT JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
//					 ON ( `last_m`.`conversationId` = `conv`.`id` )
//
//				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `interlocutorMessage`
//				 	ON ( `conv`.`id` = `interlocutorMessage`.conversationId AND `last_m`.`interlocutorMessageId` = `interlocutorMessage`.`id` )
//
//				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `initiatorMessage`
//				 	ON ( `conv`.`id` = `initiatorMessage`.conversationId AND `last_m`.`initiatorMessageId` = `initiatorMessage`.`id` )
//
//				 WHERE {$condition} AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `initiatorMessage`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
//					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `interlocutorMessage`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) )) AND `last_m`.`id` IS NOT NULL
//
//                GROUP BY `conv`.`id`
//
//                ORDER BY GREATEST( COALESCE(`initiatorMessage`.`timeStamp`, 0), COALESCE(`interlocutorMessage`.`timeStamp`, 0) ) DESC
//
//                LIMIT :from, :count";

        $sql = " SELECT `conv`.`id`,
                        `conv`.`initiatorId`,
                        `conv`.`interlocutorId`,
                        `conv`.`subject`,
                        `conv`.`read`,
                        `conv`.`viewed`,

                        `initiatorMessage`.`id` as initiatorLastMessageId,
                        `initiatorMessage`.`text` as initiatorText,
                        `initiatorMessage`.`recipientRead` as initiatorRecipientRead,
                        `initiatorMessage`.`isSystem` as initiatorMessageIsSystem,
                        `initiatorMessage`.`senderId` as `initiatorMessageSenderId`,
                        `initiatorMessage`.`recipientId` as `initiatorMessageRecipientId`,
                        `initiatorMessage`.`wasAuthorized` as `initiatorMessageWasAuthorized`,
                        `initiatorMessage`.`timeStamp` as `initiatorMessageTimestamp`

                 FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `initiatorMessage`
				 	ON ( `conv`.`lastMessageId` = `initiatorMessage`.`id` )

				 WHERE {$condition} AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `initiatorMessage`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `initiatorMessage`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) ))

                GROUP BY `conv`.`id`

                ORDER BY `conv`.`lastMessageTimestamp` DESC

                LIMIT :from, :count";

        return $this->dbo->queryForList($sql, array('user' => $userId, 'from'=>$from, 'count'=>$count));
    }

    public function countConversationListByUserId($userId, $activeModes)
    {
        if (in_array('chat', $activeModes) && in_array('mail', $activeModes))
        {
            $condition = "1 ";
        }
        else
        {
            $condition = "1 ";

            if (in_array('chat', $activeModes))
            {
                $condition .= "AND `conv`.`subject` = '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }

            if (in_array('mail', $activeModes))
            {
                $condition .= "AND `conv`.`subject` <> '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }
        }

        $sql = "SELECT COUNT(*) FROM ( SELECT `conv`.`id` as `count` FROM `" . $this->getTableName() . "` AS `conv`

                 LEFT JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE {$condition} AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `mess`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `mess`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) ))  AND `last_m`.`id` IS NOT NULL
                GROUP BY `conv`.`id`) AS `cnt`";

        return $this->dbo->queryForColumn($sql, array('user' => $userId));
    }

    public function getViewedConversationCountForConsole( $userId, $convList )
    {
        $condition = " 1 ";
        if ( !empty( $convList ) )
        {
            $condition .= " AND `conv`.`id` IN (". $this->dbo->mergeInClause($convList) .") ";
        }

        $sql = " SELECT COUNT(`conv`.`id`) FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON `last_m`.`conversationId` = `conv`.`id`
					 	AND ( ( `conv`.`initiatorId` = :user AND `last_m`.`interlocutorMessageId` > 0 AND `conv`.`deleted` != " . self::DELETED_INITIATOR . " AND NOT `conv`.`read` & " . (self::READ_INITIATOR) . " AND `conv`.`viewed` &  " . (self::VIEW_INITIATOR) . " )
					 	OR ( `conv`.`interlocutorId` = :user AND `conv`.`deleted` != " . self::DELETED_INTERLOCUTOR . "  AND  NOT `conv`.`read` & " . (self::READ_INTERLOCUTOR) . " AND `conv`.`viewed` &  " . (self::VIEW_INTERLOCUTOR) . " ) )";

        return (int) $this->dbo->queryForColumn($sql, array('user' => $userId));
    }

    public function getNewConversationCountForConsole( $userId, $convList )
    {
        $condition = " 1 ";
        if ( !empty( $convList ) )
        {
            $condition .= " AND `conv`.`id` IN (". $this->dbo->mergeInClause($convList) .") ";
        }

        $sql = " SELECT COUNT(`conv`.`id`) FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON `last_m`.`conversationId` = `conv`.`id`
					 	AND ( ( `conv`.`initiatorId` = :user AND `last_m`.`interlocutorMessageId` > 0 AND `conv`.`deleted` != " . self::DELETED_INITIATOR . " AND NOT `conv`.`read` & " . (self::READ_INITIATOR) . "  AND NOT `conv`.`viewed` &  " . (self::VIEW_INITIATOR) . " )
					 	OR ( `conv`.`interlocutorId` = :user AND `conv`.`deleted` != " . self::DELETED_INTERLOCUTOR . "  AND  NOT `conv`.`read` & " . (self::READ_INTERLOCUTOR) . " AND NOT `conv`.`viewed` &  " . (self::VIEW_INTERLOCUTOR) . " ) )
					 	WHERE ".$condition;

        return (int) $this->dbo->queryForColumn($sql, array('user' => $userId));
    }

    public function getConsoleConversationList( $activeModes, $userId, $first, $count, $lastPingTime = null, $ignoreList = array() )
    {
        if (in_array('chat', $activeModes) && in_array('mail', $activeModes))
        {
            $condition = "1 ";
        }
        else
        {
            $condition = "1 ";

            if (in_array('chat', $activeModes))
            {
                $condition .= "AND `conv`.`subject` = '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }

            if (in_array('mail', $activeModes))
            {
                $condition .= "AND `conv`.`subject` <> '".MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT."' ";
            }
        }

        $ignore = "";

        if ( !empty( $ignoreList ) )
        {
            $ignore = " AND `conv`.id NOT IN (". $this->dbo->mergeInClause($ignoreList) .") ";
        }

        $sql = " SELECT `last_m`.*, `mess`.*, `conv`.* FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE {$condition} AND ( ( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `conv`.`lastMessageTimestamp` > `conv`.`initiatorDeletedTimestamp`) AND `mess`.`id` = `last_m`.`interlocutorMessageId` )
					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `conv`.`lastMessageTimestamp` > `conv`.`interlocutorDeletedTimestamp`) AND `mess`.`id` = `last_m`.`initiatorMessageId` ) ) $ignore

				 ORDER BY if( ((`conv`.`initiatorId` = :user AND `conv`.`viewed` &  " . (self::VIEW_INITIATOR) . ") OR (`conv`.`interlocutorId` = :user AND `conv`.`viewed` &  " . (self::VIEW_INTERLOCUTOR) . ")), 1, 0  ), `mess`.`timeStamp` DESC

				 LIMIT :first, :count ";

        return $this->dbo->queryForList( $sql, array('user' => $userId, 'first' => $first, 'count' => $count, 'lastPingTime' => isset($lastPingTime) ? $lastPingTime : time() ) );
    }


    public function getMarkedUnreadConversationList( $userId, $ignoreList = array(), $activeModeList = array())
    {
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        $ignore = "";

        if ( !empty( $ignoreList ) )
        {
            $ignore = " AND `conv`.id NOT IN (". $this->dbo->mergeInClause($ignoreList) .") ";
        }

        if ( !$mailModeEnabled || !$chatModeEnabled )
        {
            if ( !$chatModeEnabled ) 
            {
                $ignore .= " AND `conv`.subject <> '" . MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT . "' ";
            }
            else 
            {
                $ignore .= " AND `conv`.subject = '" . MAILBOX_BOL_ConversationService::CHAT_CONVERSATION_SUBJECT . "' ";
            }
        }

        $ignore .= ' AND `conv`.`lastMessageId` != 0';
        $sql = " SELECT `conv`.`id` FROM `" . $this->getTableName() . "` AS `conv`

				 WHERE (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `conv`.`lastMessageTimestamp` > `conv`.`initiatorDeletedTimestamp`) ) OR ( `conv`.`interlocutorId` = :user AND ( `conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR . " OR `conv`.`lastMessageTimestamp` > `conv`.`interlocutorDeletedTimestamp`) ))
AND ( `conv`.`read` <> ".self::READ_ALL."
AND (( `conv`.`initiatorId` = :user AND NOT `conv`.`read` & ". self::READ_INITIATOR .") OR ( `conv`.`interlocutorId` = :user AND NOT `conv`.`read` & ". self::READ_INTERLOCUTOR ." ))
					 	)
					 	$ignore
        ";

        return $this->dbo->queryForColumnList( $sql, array('user' => $userId) );
    }

    public function getInboxConversationList( $userId, $first, $count )
    {
        $sql = " SELECT `conv`.*, `last_m`.*, `mess`.* FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
					 ON ( `last_m`.`conversationId` = `conv`.`id` )

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `mess`
				 	ON ( `conv`.`id` = `mess`.conversationId )

				 WHERE ( `conv`.`initiatorId` = :user AND `conv`.`deleted` != " . self::DELETED_INITIATOR . " AND `mess`.`id` = `last_m`.`interlocutorMessageId` )
					 	OR ( `conv`.`interlocutorId` = :user AND `conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " AND `mess`.`id` = `last_m`.`initiatorMessageId` )

				 ORDER BY `mess`.`timeStamp` DESC

				 LIMIT :first, :count ";

        return $this->dbo->queryForList($sql, array('user' => $userId, 'first' => $first, 'count' => $count));
    }

    public function findConversationByKeyword( $kw, $limit = null, $from = 0 )
    {
        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');
        $questionDataTable = BOL_QuestionDataDao::getInstance()->getTableName();

        $userId = PEEP::getUser()->getId();

        $limitStr = $limit === null ? '' : 'LIMIT '. intval($from) .', ' . intval($limit);


        //TODO get by username
        if ($questionName == 'username')
        {
            $join = "INNER JOIN `".BOL_UserDao::getInstance()->getTableName()."` AS us ON ( `us`.`id` = IF (`conv`.`initiatorId`=:user, `conv`.`interlocutorId`, `conv`.`initiatorId`)   )";
            $where = " `us`.`username` LIKE :kw ";
        }
        else
        {
            $join = "INNER JOIN `".$questionDataTable."` AS qd ON ( `qd`.`userId` = IF (`conv`.`initiatorId`=:user, `conv`.`interlocutorId`, `conv`.`initiatorId`)  )";
            $where = " `qd`.`questionName`=:name AND `qd`.`textValue` LIKE :kw ";
        }

        $query = " SELECT `conv`.`id`,
                        `conv`.`initiatorId`,
                        `conv`.`interlocutorId`,
                        `conv`.`subject`,
                        `conv`.`read`,
                        `conv`.`viewed`,
                        `conv`.`lastMessageTimestamp`,

                        `message`.`id` as lastMessageId,
                        `message`.`text` as lastMessageText,
                        `message`.`recipientRead` as lastMessageRecipientRead,
                        `message`.`isSystem` as lastMessageIsSystem,
                        `message`.`senderId` as lastMessageSenderId,
                        `message`.`recipientId` as lastMessageRecipientId,
                        `message`.`wasAuthorized` as lastMessageWasAuthorized

                 FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `message`
				 	ON ( `conv`.`lastMessageId` = `message`.`id` )

                 {$join}

				 WHERE ( {$where} ) AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `message`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `message`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) ))

                UNION

                SELECT `conv`.`id`,
                        `conv`.`initiatorId`,
                        `conv`.`interlocutorId`,
                        `conv`.`subject`,
                        `conv`.`read`,
                        `conv`.`viewed`,
                        `conv`.`lastMessageTimestamp`,

                        `message`.`id` as lastMessageId,
                        `message`.`text` as lastMessageText,
                        `message`.`recipientRead` as lastMessageRecipientRead,
                        `message`.`isSystem` as lastMessageIsSystem,
                        `message`.`senderId` as lastMessageSenderId,
                        `message`.`recipientId` as lastMessageRecipientId,
                        `message`.`wasAuthorized` as lastMessageWasAuthorized

                 FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `message`
				 	ON ( `conv`.`lastMessageId` = `message`.`id` )

				 WHERE conv.subject LIKE :kw AND ( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `message`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )


                UNION

                SELECT `conv`.`id`,
                        `conv`.`initiatorId`,
                        `conv`.`interlocutorId`,
                        `conv`.`subject`,
                        `conv`.`read`,
                        `conv`.`viewed`,
                        `conv`.`lastMessageTimestamp`,

                        `message`.`id` as lastMessageId,
                        `message`.`text` as lastMessageText,
                        `message`.`recipientRead` as lastMessageRecipientRead,
                        `message`.`isSystem` as lastMessageIsSystem,
                        `message`.`senderId` as lastMessageSenderId,
                        `message`.`recipientId` as lastMessageRecipientId,
                        `message`.`wasAuthorized` as lastMessageWasAuthorized

                 FROM `" . $this->getTableName() . "` AS `conv`

				 INNER JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `message`
				 	ON ( `conv`.`lastMessageId` = `message`.`id` )

				 WHERE conv.subject LIKE :kw AND (  `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `message`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ))


                GROUP BY 1
                ORDER BY 7 DESC

                ".$limitStr;
//print_r($query);
//        $query = " SELECT `conv`.`id`,
//                        `conv`.`initiatorId`,
//                        `conv`.`interlocutorId`,
//                        `conv`.`subject`,
//                        `conv`.`read`,
//                        `conv`.`viewed`,
//
//                        `last_m`.`initiatorMessageId`,
//                        `initiatorMessage`.`id` as initiatorLastMessageId,
//                        `initiatorMessage`.`text` as initiatorText,
//                        `initiatorMessage`.`recipientRead` as initiatorRecipientRead,
//                        `initiatorMessage`.`isSystem` as initiatorMessageIsSystem,
//                        `initiatorMessage`.`senderId` as `initiatorMessageSenderId`,
//                        `initiatorMessage`.`recipientId` as `initiatorMessageRecipientId`,
//                        `initiatorMessage`.`wasAuthorized` as `initiatorMessageWasAuthorized`,
//                        `initiatorMessage`.`timeStamp` as `initiatorMessageTimestamp`,
//
//                        `last_m`.`interlocutorMessageId`,
//                        `interlocutorMessage`.`id` as interlocutorLastMessageId,
//                        `interlocutorMessage`.`text` as interlocutorText,
//                        `interlocutorMessage`.`recipientRead` as interlocutorRecipientRead,
//                        `interlocutorMessage`.`isSystem` as interlocutorMessageIsSystem,
//                        `interlocutorMessage`.`senderId` as `interlocutorMessageSenderId`,
//                        `interlocutorMessage`.`recipientId` as `interlocutorMessageRecipientId`,
//                        `interlocutorMessage`.`wasAuthorized` as `interlocutorMessageWasAuthorized`,
//                        `interlocutorMessage`.`timeStamp` as `interlocutorMessageTimestamp`
//
//                 FROM `" . $this->getTableName() . "` AS `conv`
//
//				 INNER JOIN `" . MAILBOX_BOL_LastMessageDao::getInstance()->getTableName() . "` AS `last_m`
//					 ON ( `last_m`.`conversationId` = `conv`.`id` )
//
//				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `interlocutorMessage`
//				 	ON ( `conv`.`id` = `interlocutorMessage`.conversationId AND `last_m`.`interlocutorMessageId` = `interlocutorMessage`.`id` )
//
//				 LEFT JOIN `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` AS `initiatorMessage`
//				 	ON ( `conv`.`id` = `initiatorMessage`.conversationId AND `last_m`.`initiatorMessageId` = `initiatorMessage`.`id` )
//
//                 {$join}
//
//				 WHERE ( {$where} ) AND (( `conv`.`initiatorId` = :user AND (`conv`.`deleted` != " . self::DELETED_INITIATOR . " OR `initiatorMessage`.`timeStamp`>`conv`.`initiatorDeletedTimestamp` )  )
//					 	OR ( `conv`.`interlocutorId` = :user AND (`conv`.`deleted` != "  . self::DELETED_INTERLOCUTOR .  " OR `interlocutorMessage`.`timeStamp`>`conv`.`interlocutorDeletedTimestamp` ) )) AND `last_m`.`id` IS NOT NULL
//
//                GROUP BY `conv`.`id`
//
//                ORDER BY GREATEST( COALESCE(`initiatorMessage`.`timeStamp`, 0), COALESCE(`interlocutorMessage`.`timeStamp`, 0) ) DESC
//
//                ".$limitStr;

        $conversationItemList = PEEP::getDbo()->queryForList($query, array(
            'kw' => '%' . $kw . '%',
            'user' => $userId,
            'name' => $questionName
        ));

        foreach($conversationItemList as $i => $conversation)
        {
            $conversationItemList[$i]['timeStamp'] = (int)$conversation['lastMessageTimestamp'];
            $conversationItemList[$i]['lastMessageSenderId'] = $conversation['lastMessageSenderId'];
            $conversationItemList[$i]['isSystem'] = $conversation['lastMessageIsSystem'];
            $conversationItemList[$i]['text'] = $conversation['lastMessageText'];

            $conversationItemList[$i]['lastMessageId'] = $conversation['lastMessageId'];
            $conversationItemList[$i]['recipientRead'] = $conversation['lastMessageRecipientRead'];
            $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['lastMessageRecipientId'];
            $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['lastMessageWasAuthorized'];
        }

//        foreach($conversationItemList as $i => $conversation)
//        {
//            if ((int)$conversation['initiatorMessageTimestamp'] > (int)$conversation['interlocutorMessageTimestamp'])
//            {
//                $conversationItemList[$i]['timeStamp'] = (int)$conversation['initiatorMessageTimestamp'];
//                $conversationItemList[$i]['lastMessageSenderId'] = $conversation['initiatorMessageSenderId'];
//                $conversationItemList[$i]['isSystem'] = $conversation['initiatorMessageIsSystem'];
//                $conversationItemList[$i]['text'] = $conversation['initiatorText'];
//
//                $conversationItemList[$i]['lastMessageId'] = $conversation['initiatorLastMessageId'];
//                $conversationItemList[$i]['recipientRead'] = $conversation['initiatorRecipientRead'];
//                $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['initiatorMessageRecipientId'];
//                $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['initiatorMessageWasAuthorized'];
//            }
//            else
//            {
//                $conversationItemList[$i]['timeStamp'] = (int)$conversation['interlocutorMessageTimestamp'];
//                $conversationItemList[$i]['lastMessageSenderId'] = $conversation['interlocutorMessageSenderId'];
//                $conversationItemList[$i]['isSystem'] = $conversation['interlocutorMessageIsSystem'];
//                $conversationItemList[$i]['text'] = $conversation['interlocutorText'];
//
//                $conversationItemList[$i]['lastMessageId'] = $conversation['interlocutorLastMessageId'];
//                $conversationItemList[$i]['recipientRead'] = $conversation['interlocutorRecipientRead'];
//                $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['interlocutorMessageRecipientId'];
//                $conversationItemList[$i]['lastMessageWasAuthorized'] = $conversation['interlocutorMessageWasAuthorized'];
//            }
//        }

        return $conversationItemList;
    }


    /**
     * @param $userId
     * @return MAILBOX_BOL_Conversation
     */
    public function findUserLastConversation($userId)
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('initiatorId', $userId);
        $example->setOrder('createStamp DESC');
        $example->setLimitClause(0,1);

        return $this->findObjectByExample($example);
    }
}