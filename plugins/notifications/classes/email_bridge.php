<?php

class NOTIFICATIONS_CLASS_EmailBridge
{
    /**
     * Class instance
     *
     * @var NOTIFICATIONS_CLASS_EmailBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_CLASS_EmailBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
    }

    public function sendNotification( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $userId = $params['userId'];

        $itemEventParams = array_merge(array(
            'data' => $data
        ), $params);

        $itemEvent = new PEEP_Event('notifications.on_item_send', $params, $data);
        PEEP::getEventManager()->trigger($itemEvent);

        $notificationItem = $itemEvent->getParams();
        $notificationItem['data'] = $itemEvent->getData();

        $this->service->sendPermittedNotifications($userId, array($notificationItem));
    }

    public function sendList( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];

        $notifications = $this->service->findNotificationListForSend($userIdList);

        $notificationIds = array();
        foreach ( $notifications as $notification )
        {
            $event->add(array(
                'pluginKey' => $notification->pluginKey,
                'entityType' => $notification->entityType,
                'entityId' => $notification->entityId,
                'userId' => $notification->userId,
                'action' => $notification->action,
                'time' => $notification->timeStamp,
                'viewed' => (bool) $notification->viewed,

                'data' => $notification->getData()
            ));

            $notificationIds[] = $notification->id;
        }

        $this->service->markNotificationsSentByIds($notificationIds);
    }

    public function genericAfterInits()
    {
        PEEP::getEventManager()->bind('notifications.send', array($this, 'sendNotification'));
    }

    public function init()
    {
        $this->genericInit();
    }
    
    public function genericInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($this, 'genericAfterInits'));
        PEEP::getEventManager()->bind('notifications.send_list', array($this, 'sendList'));
    }
}