<?php

class PVISITORS_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var PVISITORS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        
    }

    /**
     * Returns class instance
     *
     * @return PVISITORS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function onProfilePageRender( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityId']) || empty($params['placeName']) || $params['placeName'] != 'profile' )
        {
            return;
        }

        $userId = (int) $params['entityId'];
        $viewerId = PEEP::getUser()->getId();

        $authService = BOL_AuthorizationService::getInstance();
        $isAdmin = $authService->isActionAuthorizedForUser($viewerId, 'admin') || $authService->isActionAuthorizedForUser($viewerId, 'base');

        if ( $userId && $viewerId && ($viewerId != $userId) && !$isAdmin )
        {
            PVISITORS_BOL_Service::getInstance()->trackVisit($userId, $viewerId);
        }
    }

    public function trackVisit( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['visitorId']) )
        {
            return;
        }

        $userId = $params['userId'];
        $visitorId = $params['visitorId'];

        $authService = BOL_AuthorizationService::getInstance();
        $isAdmin = $authService->isActionAuthorizedForUser($visitorId, 'admin') || $authService->isActionAuthorizedForUser($visitorId, 'base');

        if ( $userId && $visitorId && ($visitorId != $userId) && !$isAdmin )
        {
            PVISITORS_BOL_Service::getInstance()->trackVisit($userId, $visitorId);
        }
    }

    public function onUserUnregister( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];

        PVISITORS_BOL_Service::getInstance()->deleteUserVisitors($userId);
    }

    public function getList( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $page = empty($params['page']) ? 1 : $params['page'];
        $limit = empty($params['limit']) ? 1000000 : $params['limit'];

        $users = PVISITORS_BOL_Service::getInstance()->findVisitorUsers($userId, $page, $limit);
        $visitorsIdList = array();
        foreach ( $users as $user )
        {
            $visitorsIdList[] = $user->id;
        }

        $visitors = PVISITORS_BOL_Service::getInstance()->findVisitorsByVisitorIds($userId, $visitorsIdList);
        $out = array();

        foreach ( $visitors as $visitor )
        {
            $out[] = array(
                "userId" => $visitor->visitorId,
                "viewed" => $visitor->viewed,
                "timeStamp" => $visitor->visitTimestamp
            );
        }

        $event->setData($out);

        return $out;
    }

    public function getNewCount( PEEP_Event $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        $count = PVISITORS_BOL_Service::getInstance()->findNewVisitorsCount($userId);

        $event->setData($count);

        return $count;
    }

    public function markViewed( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['visitorIds']) )
        {
            return;
        }

        $userId = $params['userId'];
        $visitorIds = $params['visitorIds'];

        PVISITORS_BOL_Service::getInstance()->setViewedStatusByVisitorIds($userId, $visitorIds);
    }

    public function genericInit()
    {
        $em = PEEP::getEventManager();

        $em->bind("visitors.get_visitors_list", array($this, "getList"));
        $em->bind("visitors.get_new_visitors_count", array($this, "getNewCount"));
        $em->bind("visitors.mark_visitors_viewed", array($this, "markViewed"));
        $em->bind("visitors.track_visit", array($this, "trackVisit"));

        $em->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
    }

    public function init()
    {
        $this->genericInit();
        $em = PEEP::getEventManager();

        $em->bind('base.widget_panel.content.top', array($this, 'onProfilePageRender'));
    }
}
