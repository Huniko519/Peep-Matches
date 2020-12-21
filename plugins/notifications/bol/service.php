<?php

class NOTIFICATIONS_BOL_Service
{
    const SCHEDULE_IMMEDIATELY = 'immediately';
    const SCHEDULE_AUTO = 'auto';
    const SCHEDULE_NEVER = 'never';

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var NOTIFICATIONS_BOL_RuleDao
     */
    private $ruleDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_UnsubscribeDao
     */
    private $unsubscribeDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_ScheduleDao
     */
    private $scheduleDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_SendQueueDao
     */
    private $sendQueueDao;

    /**
     *
     * @var NOTIFICATIONS_BOL_NotificationDao
     */
    private $notificationDao;

    private $defaultRuleList = array();

    public function __construct()
    {
        $this->ruleDao = NOTIFICATIONS_BOL_RuleDao::getInstance();
        $this->unsubscribeDao = NOTIFICATIONS_BOL_UnsubscribeDao::getInstance();
        $this->notificationDao = NOTIFICATIONS_BOL_NotificationDao::getInstance();
        $this->scheduleDao = NOTIFICATIONS_BOL_ScheduleDao::getInstance();
        $this->sendQueueDao = NOTIFICATIONS_BOL_SendQueueDao::getInstance();
    }

    public function collectActionList()
    {
        if ( empty($this->defaultRuleList) )
        {
            $event = new BASE_CLASS_EventCollector('notifications.collect_actions');
            PEEP::getEventManager()->trigger($event);

            $eventData = $event->getData();
            foreach ( $eventData as $item )
            {
                $this->defaultRuleList[$item['action']] = $item;
            }
        }

        return $this->defaultRuleList;
    }

    public function findRuleList( $userId, $actions = null )
    {
        $out = array();
        $list = $this->ruleDao->findRuleList($userId, $actions);
        foreach ( $list as $item )
        {
            $out[$item->action] = $item;
        }

        return $out;
    }

    public function saveRule( NOTIFICATIONS_BOL_Rule $rule )
    {
        $this->ruleDao->save($rule);
    }

    public function findUserIdByUnsubscribeCode( $code )
    {
        $dto = $this->unsubscribeDao->findByCode($code);

        return  empty($dto) ? null : $dto->userId;
    }

    private function getUnsubscribeCodeLifeTime()
    {
        return 60 * 60 * 24 * 7;
    }

    public function deleteExpiredUnsubscribeCodeList()
    {
        $time = time() - $this->getUnsubscribeCodeLifeTime();
        $this->unsubscribeDao->deleteExpired($time);
    }

    public function generateUnsubscribeCode( BOL_User $user )
    {
        $code = md5($user->email);
        $dto = new NOTIFICATIONS_BOL_Unsubscribe();
        $dto->userId = $user->id;
        $dto->code = $code;
        $dto->timeStamp = time();

        $this->unsubscribeDao->save($dto);

        return $code;
    }

    
    public function isNotificationPermited( $userId, $action )
    {
        $defaultRules = $this->collectActionList();
        $rules = $this->findRuleList($userId);
        
        if ( isset($rules[$action]) )
        {
            return (bool) $rules[$action]->checked;
        }
        
        return !empty($defaultRules[$action]['selected']);
    }
    
    public function sendPermittedNotifications( $userId, $notificationList )
    {
        $defaultRules = $this->collectActionList();
        $rules = $this->findRuleList($userId);

        $listToSend = array();
        foreach ( $notificationList as $notification )
        {
            $action = $notification['action'];

            if ( isset($rules[$action]) )
            {
                if ( !$rules[$action]->checked )
                {
                    continue;
                }
            }
            else
            {
                if ( empty($defaultRules[$action]['selected']) )
                {
                    continue;
                }
            }

            $listToSend[] = $notification;
        }

        $this->sendNotifications($userId, $listToSend, false);
    }

    public function sendNotifications( $userId, $notifications )
    {
        if ( empty($notifications) )
        {
            return;
        }

        $cmp = new NOTIFICATIONS_CMP_Notification($userId);

        foreach ( $notifications as $item )
        {
            $data = $item['data'];
            $params = $item;
            $onEvent = new PEEP_Event('notifications.on_item_send', $params, $data);
            PEEP::getEventManager()->trigger($onEvent);

            $item['data'] = $onEvent->getData();

            $cmp->addItem($item);
        }

        $this->sendProcess($userId, $cmp);
    }

    private function sendProcess( $userId, NOTIFICATIONS_CMP_Notification $cmp )
    {
        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);

        if ( empty($user) )
        {
            return false;
        }

        $email = $user->email;
        $unsubscribeCode = $this->generateUnsubscribeCode($user);

        $cmp->setUnsubscribeCode($unsubscribeCode);

        $txt = $cmp->getTxt();
        $html = $cmp->getHtml();

        $subject = $cmp->getSubject();

        try
        {
            $mail = PEEP::getMailer()->createMail()
                ->addRecipientEmail($email)
                ->setTextContent($txt)
                ->setHtmlContent($html)
                ->setSubject($subject);

            PEEP::getMailer()->send($mail);
        }
        catch ( Exception $e )
        {
            //Skip invalid notification
        }
    }



    public function findNotificationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        return $this->notificationDao->findNotificationList($userId, $beforeStamp, $ignoreIds, $count);
    }

    public function findNewNotificationList( $userId, $afterStamp )
    {
        return $this->notificationDao->findNewNotificationList($userId, $afterStamp);
    }

    public function findNotificationListForSend( $userIdList )
    {
        return $this->notificationDao->findNotificationListForSend($userIdList);
    }

    public function findNotificationCount( $userId, $viewed = null, $exclude = null )
    {
        return $this->notificationDao->findNotificationCount($userId, $viewed, $exclude);
    }

    public function saveNotification( NOTIFICATIONS_BOL_Notification $notification )
    {
        $this->notificationDao->saveNotification($notification);
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return NOTIFICATIONS_BOL_Notification
     */
    public function findNotification( $entityType, $entityId, $userId )
    {
        return $this->notificationDao->findNotification($entityType, $entityId, $userId);
    }

    public function markNotificationsViewedByIds( $idList, $viewed = true )
    {
        $this->notificationDao->markViewedByIds($idList, $viewed);
    }

    public function markNotificationsViewedByUserId( $userId, $viewed = true )
    {
        $this->notificationDao->markViewedByUserId($userId, $viewed);
    }

    public function markNotificationsSentByIds( $idList, $sent = true )
    {
        $this->notificationDao->markSentByIds($idList, $sent);
    }

    public function deleteNotification( $entityType, $entityId, $userId )
    {
        $this->notificationDao->deleteNotification($entityType, $entityId, $userId);
    }

    public function deleteExpiredNotification()
    {
        $this->notificationDao->deleteExpired();
    }

    public function deleteNotificationByEntity( $entityType, $entityId )
    {
        $this->notificationDao->deleteNotificationByEntity($entityType, $entityId);
    }

    public function deleteNotificationByPluginKey( $pluginKey )
    {
        $this->notificationDao->deleteNotificationByPluginKey($pluginKey);
    }

    public function setNotificationStatusByPluginKey( $pluginKey, $status )
    {
        $this->notificationDao->setNotificationStatusByPluginKey($pluginKey, $status);
    }

    public function getDefaultSchedule()
    {
        return self::SCHEDULE_AUTO;
    }

    public function getSchedule( $userId )
    {
        $entity = $this->scheduleDao->findByUserId($userId);

        return $entity === null ? $this->getDefaultSchedule() : $entity->schedule;
    }

    public function setSchedule( $userId, $schedule )
    {
        $entity = $this->scheduleDao->findByUserId($userId);

        if ( $entity === null )
        {
            $entity = new NOTIFICATIONS_BOL_Schedule();
            $entity->userId = $userId;
        }
        else if ( $entity->schedule == $schedule )
        {
            return false;
        }

        $entity->schedule = $schedule;

        $this->scheduleDao->save($entity);

        return true;
    }

    public function fillSendQueue( $period = null )
    {
        $this->sendQueueDao->fillData($period, $this->getDefaultSchedule());
    }

    public function getSendQueueLength()
    {
        return $this->sendQueueDao->countAll();
    }

    public function findUserIdListForSend( $count )
    {
        $list = $this->sendQueueDao->findList($count);

        if ( empty($list) )
        {
            return array();
        }

        $userIds = array();
        $ids = array();
        foreach ( $list as $item )
        {
            $ids[] = $item->id;
            $userIds[] = $item->userId;
        }

        $this->sendQueueDao->deleteByIdList($ids);

        return $userIds;
    }
}
