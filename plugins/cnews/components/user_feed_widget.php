<?php

class CNEWS_CMP_UserFeedWidget extends CNEWS_CMP_FeedWidget
{

    private $userId;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);

        $userId = $paramObj->additionalParamList['entityId'];

        // privacy check
        $viewerId = PEEP::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = PEEP::getUser()->isAuthorized('cnews');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => CNEWS_BOL_Service::PRIVACY_ACTION_VIEW_MY_FEED, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new PEEP_Event('privacy_check_permission', $privacyParams);

            try {
                PEEP::getEventManager()->trigger($event);
            }
            catch ( RedirectException $e )
            {
                $this->setVisible(false);

                return;
            }
        }

        $feed = $this->createFeed('user', $userId);

        $isBloacked = BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $userId);
        
        if ( PEEP::getUser()->isAuthorized('base', 'add_comment') )
        {
            if ( $isBloacked )
            {
                $feed->addStatusMessage(PEEP::getLanguage()->text("base", "user_block_message"));
            }
            else
            {
                $visibility = CNEWS_BOL_Service::VISIBILITY_FULL;
                $feed->addStatusForm('user', $userId, $visibility);
            }
        } 
        else 
        {
            $actionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'add_comment');
            
            if ( $actionStatus["status"] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $feed->addStatusMessage($actionStatus["msg"]);
            }
        }

        $feed->setDisplayType(CNEWS_CMP_Feed::DISPLAY_TYPE_ACTIVITY);
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
        $driver = PEEP::getClassInstance("CNEWS_CLASS_FeedDriver");
        
        return PEEP::getClassInstance("CNEWS_CMP_Feed", $driver, $feedType, $feedId);
    }
}