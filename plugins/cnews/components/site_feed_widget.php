<?php

class CNEWS_CMP_SiteFeedWidget extends CNEWS_CMP_FeedWidget
{
    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $feed = $this->createFeed('site', null);
        $feed->setDisplayType(CNEWS_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
        $enabled = PEEP::getConfig()->getValue('cnews', 'index_status_enabled');

        if ( $enabled && PEEP::getUser()->isAuthenticated() && PEEP::getUser()->isAuthorized('cnews', 'allow_status_update') )
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
        $driver = PEEP::getClassInstance("CNEWS_CLASS_SiteDriver");
        
        return PEEP::getClassInstance("CNEWS_CMP_Feed", $driver, $feedType, $feedId);
    }
}