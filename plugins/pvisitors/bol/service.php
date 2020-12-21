<?php

final class PVISITORS_BOL_Service
{
    /**
     * @var PVISITORS_BOL_VisitorDao
     */
    private $visitorDao;

    /**
     * Class instance
     *
     * @var PVISTORS_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->visitorDao = PVISITORS_BOL_VisitorDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PVISITORS_BOL_Service
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
     * @param $userId
     * @param $visitorId
     * @return bool
     */
    public function trackVisit( $userId, $visitorId )
    {
        if ( !$userId || !$visitorId || ($visitorId == $userId) || BOL_AuthorizationService::getInstance()->isModerator($visitorId) )
        {
            return;
        }

        $visitor = $this->visitorDao->findVisitor($userId, $visitorId);

        if ( $visitor )
        {
            $visitor->visitTimestamp = time();
            $this->visitorDao->save($visitor);

            return true;
        }

        $visitor = new PVISITORS_BOL_Visitor();
        $visitor->userId = $userId;
        $visitor->visitorId = $visitorId;
        $visitor->viewed = 0;
        $visitor->visitTimestamp = time();

        $this->visitorDao->save($visitor);

        return true;
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findVisitorsForUser( $userId, $page, $limit )
    {
        if ( !$userId )
        {
            return array();
        }

        $visitors = $this->visitorDao->findUserVisitors($userId, $page, $limit);

        foreach ( $visitors as &$g )
        {
            $g->visitTimestamp = UTIL_DateTime::formatDate($g->visitTimestamp, false);
        }

        return $visitors;
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findVisitorUsers( $userId, $page, $limit )
    {
        if ( !$userId )
        {
            return array();
        }

        $visitors = $this->visitorDao->findVisitorUsers($userId, $page, $limit);

        return $visitors;
    }

    /**
     * @param $userId
     * @return int
     */
    public function findNewVisitorsCount( $userId )
    {
        if ( !$userId )
        {
            return 0;
        }

        return (int) $this->visitorDao->countNewVisitors($userId);
    }

    /**
     * @param $userId
     * @return int
     */
    public function countVisitorsForUser( $userId )
    {
        return $this->visitorDao->countUserVisitors($userId);
    }

    /**
     * @return bool
     */
    public function checkExpiredVisitors()
    {
        $months = (int) PEEP::getConfig()->getValue('pvisitors', 'store_period');
        $timestamp = $months * 30 * 24 * 60 * 60;

        $this->visitorDao->deleteExpired($timestamp);

        return true;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function deleteUserVisitors( $userId )
    {
        $this->visitorDao->deleteUserVisitors($userId);

        return true;
    }

    public function getViewedStatusByVisitorsIds( $userId, $visitorIds )
    {
        return $this->visitorDao->getViewedStatusByVisitorIds($userId, $visitorIds);
    }

    public function findVisitorsByVisitorIds( $userId, $visitorIds )
    {
        return $this->visitorDao->findVisitorsByVisitorIds($userId, $visitorIds);
    }

    public function setViewedStatusByVisitorIds( $userId, $visitorIds, $viewed = true )
    {
        return $this->visitorDao->setViewedStatusByVisitorIds($userId, $visitorIds, $viewed);
    }
}
