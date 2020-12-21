<?php

class CNEWS_CTRL_Feed extends PEEP_ActionController
{
    /**
     *
     * @var CNEWS_BOL_Service
     */
    protected $service;

    public function __construct()
    {
        $this->service = CNEWS_BOL_Service::getInstance();
    }

    /**
     * 
     * @param CNEWS_CLASS_Driver $driver
     * @param string $feedType
     * @param string $feedId
     * @return CNEWS_CMP_Feed
     */
    protected function getFeed( CNEWS_CLASS_Driver $driver, $feedType, $feedId )
    {
        return PEEP::getClassInstance("CNEWS_CMP_Feed", $driver, $feedType, $feedId);
    }
    
    public function viewItem( $params )
    {
        $actionId = (int) $params['actionId'];
        $feedType = empty($_GET['ft']) ? 'site' : $_GET['ft'];
        $feedId = empty($_GET['fi']) ? null : $_GET['fi'];

        $driverClasses = array(
            "site" => "CNEWS_CLASS_SiteDriver",
            "my" => "CNEWS_CLASS_UserDriver"
        );
        
        $driverClass = empty($driverClasses[$feedType]) 
                ? "CNEWS_CLASS_FeedDriver"
                : $driverClasses[$feedType];
        
        $driver = PEEP::getClassInstance($driverClass);
        
        $driver->setup(array(
            'feedType' => $feedType,
            'feedId' => $feedId
        ));

        $action = $driver->getActionById($actionId);

        if ( empty($action) )
        {
            throw new Redirect404Exception();
        }

        $feed = $this->getFeed($driver, $feedType, $feedId);
        $feed->setup(array(
            'viewMore' => false
        ));

        $feed->setDisplayType(CNEWS_CMP_Feed::DISPLAY_TYPE_PAGE);
        $feed->addAction($action);

        $this->addComponent('action', $feed);
        
        $this->assign("entity", array(
            "type" => $action->getEntity()->type,
            "id" => $action->getEntity()->id
        ));
    }

    public function follow()
    {
        $userId = (int) $_GET['userId'];
        $backUri = $_GET['backUri'];

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( empty($userId) )
        {
            throw new InvalidArgumentException('Invalid parameter `userId`');
        }

        $eventParams = array(
            'userId' => PEEP::getUser()->getId(),
            'feedType' => 'user',
            'feedId' => $userId
        );

        PEEP::getEventManager()->trigger( new PEEP_Event('feed.add_follow', $eventParams) );

        $backUrl = PEEP_URL_HOME . $backUri;
        $username = BOL_UserService::getInstance()->getDisplayName($userId);

        if ( PEEP::getRequest()->isAjax() )
        {
            exit(json_encode(array(
                'message' => PEEP::getLanguage()->text('cnews', 'follow_complete_message', array('username' => $username))
            )));
        }
        else
        {
            PEEP::getFeedback()->info(PEEP::getLanguage()->text('cnews', 'follow_complete_message', array('username' => $username)));
            $this->redirect($backUrl);
        }
    }

    public function unFollow()
    {
        $userId = (int) $_GET['userId'];
        $backUri = $_GET['backUri'];

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( empty($userId) )
        {
            throw new InvalidArgumentException('Invalid parameter `userId`');
        }

        $this->service->removeFollow(PEEP::getUser()->getId(), 'user', $userId);

        $backUrl = PEEP_URL_HOME . $backUri;
        $username = BOL_UserService::getInstance()->getDisplayName($userId);

        if ( PEEP::getRequest()->isAjax() )
        {
            exit(json_encode(array(
                'message' => PEEP::getLanguage()->text('cnews', 'unfollow_complete_message', array('username' => $username))
            )));
        }
        else
        {
            PEEP::getFeedback()->info(PEEP::getLanguage()->text('cnews', 'unfollow_complete_message', array('username' => $username)));
            $this->redirect($backUrl);
        }
    }
}