<?php

class NOTIFICATIONS_Cron extends PEEP_Cron
{
    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = NOTIFICATIONS_BOL_Service::getInstance();

        $this->addJob('expireUnsubscribe', 60 * 60);
        $this->addJob('deleteExpired', 60 * 60);

        $this->addJob('fillSendQueue', 10);
    }

    /**
     *  Return run interval in minutes
     *
     * @return int
     */
    public function getRunInterval()
    {
        return 1;
    }

    public function expireUnsubscribe()
    {
        $this->service->deleteExpiredUnsubscribeCodeList();
    }

    public function deleteExpired()
    {
        $this->service->deleteExpiredNotification();
    }


    public function fillSendQueue()
    {
        if ( $this->service->getSendQueueLength() == 0 )
        {
            $this->service->fillSendQueue(24 * 3600);
        }
    }

    public function run()
    {
        $users = $this->service->findUserIdListForSend(100);

        if ( empty($users) )
        {
            return;
        }

        $listEvent = new BASE_CLASS_EventCollector('notifications.send_list', array(
            'userIdList' => $users
        ));

        PEEP::getEventManager()->trigger($listEvent);

        $notifications = array();
        foreach ( $listEvent->getData() as $notification )
        {
            $itemEvent = new PEEP_Event('notifications.on_item_send', $notification, $notification['data']);
            PEEP::getEventManager()->trigger($itemEvent);

            $notification['data'] = $itemEvent->getData();

            $notifications[$notification['userId']][] = $notification;
        }

        foreach ( $notifications as $userId => $notificationList )
        {
            $this->service->sendPermittedNotifications($userId, $notificationList);
        }
    }
}