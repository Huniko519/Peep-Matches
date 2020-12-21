<?php

class NOTIFICATIONS_CLASS_ConsoleBridge
{
    /**
     * Class instance
     *
     * @var NOTIFICATIONS_CLASS_ConsoleBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return NOTIFICATIONS_CLASS_ConsoleBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const CONSOLE_ITEM_KEY = 'notification';

    /**
     *
     * @var NOTIFICATIONS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = NOTIFICATIONS_BOL_Service::getInstance();
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            return;
        }

        $item = new NOTIFICATIONS_CMP_ConsoleItem();
        $event->addItem($item, 3);
    }

    public function addNotification( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['entityType']) || empty($params['entityId']) || empty($params['userId']) || empty($params['pluginKey']) )
        {
            throw new InvalidArgumentException('`entityType`, `entityId`, `userId`, `pluginKey` are required');
        }

        if ( !$this->service->isNotificationPermited($params['userId'], $params['action']) )
        {
            return;
        }

        $notification = $this->service->findNotification($params['entityType'], $params['entityId'], $params['userId']);

        if ( $notification === null )
        {
            $notification = new NOTIFICATIONS_BOL_Notification();
            $notification->entityType = $params['entityType'];
            $notification->entityId = $params['entityId'];
            $notification->userId = $params['userId'];
            $notification->pluginKey = $params['pluginKey'];
            $notification->action = $params['action'];
        }
        else
        {
            $notification->viewed = 0;

            $dublicateParams = array(
                'originalEvent' => $event,
                'notificationDto' => $notification,
                'oldData' => $notification->getData()
            );

            $dublicateParams = array_merge($params, $dublicateParams);

            $dublicateEvent = new PEEP_Event('notifications.on_dublicate', $dublicateParams, $data);
            PEEP::getEventManager()->trigger($dublicateEvent);

            $data = $dublicateEvent->getData();
        }

        $notification->timeStamp = empty($params['time']) ? time() : $params['time'];
        $notification->active = isset($params['active']) ? (bool)$params['active'] : true;
        $notification->setData($data);

        $this->service->saveNotification($notification);
    }

    public function removeNotification( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || empty($params['entityId']) )
        {
            throw new InvalidArgumentException('`entityType` and `entityId` params are required');
        }

        $userId = empty($params['userId']) ? null : $params['userId'];
        $entityType = $params['entityType'];
        $entityId = $params['entityId'];

        if ( $userId !== null )
        {
            $this->service->deleteNotification($entityType, $entityId, $userId);
        }
        else
        {
            $this->service->deleteNotificationByEntity($entityType, $entityId);
        }
    }


    /* Console list */

    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        $userId = PEEP::getUser()->getId();
        $data = $event->getItemData(self::CONSOLE_ITEM_KEY);

        $newNotificationCount = $this->service->findNotificationCount($userId, false);
        $allNotificationCount = $this->service->findNotificationCount($userId);

        $data['counter'] = array(
            'all' => $allNotificationCount,
            'new' => $newNotificationCount
        );

        $event->setItemData(self::CONSOLE_ITEM_KEY, $data);
    }


    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $userId = PEEP::getUser()->getId();

        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;

        }

        $loadItemsCount = 15;
        $notifications = $this->service->findNotificationList($userId, $params['console']['time'], $params['ids'], $loadItemsCount);
        $notificationIds = array();

        $data['listFull'] = count($notifications) < $loadItemsCount;

        foreach ( $notifications as $notification )
        {
            $notificationData = $notification->getData();
            
            $itemEvent = new PEEP_Event('notifications.on_item_render', array(
                'key' => 'notification_' . $notification->id,
                'entityType' => $notification->entityType,
                'entityId' => $notification->entityId,
                'pluginKey' => $notification->pluginKey,
                'userId' => $notification->userId,
                'viewed' => (bool) $notification->viewed,
                'data' => $notificationData
            ), $notificationData);

            PEEP::getEventManager()->trigger($itemEvent);

            $item = $itemEvent->getData();

            if ( empty($item) )
            {
                continue;
            }
            
            $notificationIds[] = $notification->id;

            $event->addItem($item, $notification->id);
        }

        $event->setData($data);
        $this->service->markNotificationsViewedByIds($notificationIds);
    }

    private function processDataInterface( $params, $data )
    {
        if ( empty($data['avatar']) )
        {
            return array();
        }

        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                $data[$langProperty] = PEEP::getLanguage()->text($key[0], $key[1], $vars);
            }
        }

        if ( empty($data['string']) )
        {
            return array();
        }

        if ( !empty($data['contentImage']) )
        {
            $data['contentImage'] = is_string($data['contentImage'])
                ? array( 'src' => $data['contentImage'] )
                : $data['contentImage'];
        }
        else
        {
            $data['contentImage'] = null;
        }
        
        if ( !empty($data["avatar"]["userId"]) )
        {
            $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($data["avatar"]["userId"]));
            $data["avatar"] = $avatarData[$data["avatar"]["userId"]];
        }
        
        $data['contentImage'] = empty($data['contentImage']) ? array() : $data['contentImage'];
        $data['toolbar'] = empty($data['toolbar']) ? array() : $data['toolbar'];
        $data['key'] = isset($data['key']) ? $data['key'] : $params['key'];
        $data['viewed'] = isset($params['viewed']) && !$params['viewed'];
        $data['url'] = isset($data['url']) ? $data['url'] : null;

        return $data;
    }

    public function renderItem( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if (is_string($data) )
        {
            return;
        }

        $interface = $this->processDataInterface($params, $data);

        if ( empty($interface) )
        {
            return;
        }

        $item = new NOTIFICATIONS_CMP_NotificationItem();
        $item->setAvatar($interface['avatar']);
        $item->setContent($interface['string']);
        $item->setKey($interface['key']);
        $item->setToolbar($interface['toolbar']);
        $item->setContentImage($interface['contentImage']);
        $item->setUrl($interface['url']);

        if ( $interface['viewed'] )
        {
            $item->addClass('peep_console_new_message');
        }

        $event->setData($item->render());
    }


    public function pluginActivate( PEEP_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setNotificationStatusByPluginKey($pluginKey, true);
    }

    public function pluginDeactivate( PEEP_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->setNotificationStatusByPluginKey($pluginKey, false);
    }

    public function pluginUninstall( PEEP_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        $this->service->deleteNotificationByPluginKey($pluginKey);
    }

    public function afterInits()
    {
        PEEP::getEventManager()->bind('notifications.on_item_render', array($this, 'renderItem'));
    }
    
    public function genericAfterInits()
    {
        PEEP::getEventManager()->bind('notifications.remove', array($this, 'removeNotification'));
    }

    public function init()
    {
        $this->genericInit();
        
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($this, 'afterInits'));

        PEEP::getEventManager()->bind(PEEP_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'pluginActivate'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'pluginDeactivate'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'pluginUninstall'));

        PEEP::getEventManager()->bind('console.load_list', array($this, 'loadList'));
        PEEP::getEventManager()->bind('console.ping', array($this, 'ping'));
        PEEP::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));
    }
    
    public function genericInit()
    {
        PEEP::getEventManager()->bind('notifications.add', array($this, 'addNotification'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($this, 'genericAfterInits'));
    }
}