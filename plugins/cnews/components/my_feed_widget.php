<?php

class CNEWS_CMP_MyFeedWidget extends CNEWS_CMP_FeedWidget
{

    private $userId;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $feed = $this->createFeed('my', PEEP::getUser()->getId());
        $feed->setDisplayType(CNEWS_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
        
        if ( PEEP::getUser()->isAuthorized('cnews', 'allow_status_update') )
        {
            $feed->addStatusForm('user', PEEP::getUser()->getId());
        }

        $this->setFeed( $feed );
    }

    /**
     * 
     * @param string $feedType
     * @param int $feedId
     * @return CNEWS_CMP_Feed
     */
    protected function createFeed( $feedType, $feedId )
    {
        $driver = PEEP::getClassInstance("CNEWS_CLASS_UserDriver");
        
        return PEEP::getClassInstance("CNEWS_CMP_Feed", $driver, $feedType, $feedId);
    }
    
    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}