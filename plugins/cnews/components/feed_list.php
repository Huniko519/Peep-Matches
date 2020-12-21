<?php

class CNEWS_CMP_FeedList extends PEEP_Component
{
    private $feed = array();
    private $sharedData = array();
    private $displayType;

    public function __construct( $actionList, $data )
    {
        parent::__construct();

        $this->feed = $actionList;
        $this->displayType = CNEWS_CMP_Feed::DISPLAY_TYPE_ACTION;

        $this->sharedData['feedAutoId'] = $data['feedAutoId'];
        $this->sharedData['displayType'] = $data['displayType'];
        $this->sharedData['feedType'] = $data['feedType'];
        $this->sharedData['feedId'] = $data['feedId'];
        $this->sharedData['configs'] = PEEP::getConfig()->getValues('cnews');

        $userIds = array();
        $entityList = array();
        foreach ( $this->feed as $action )
        {
            /* @var $action CNEWS_CLASS_Action */
            $userIds[$action->getUserId()] = $action->getUserId();
            $entityList[] = array(
                'entityType' => $action->getEntity()->type,
                'entityId' => $action->getEntity()->id,
                'pluginKey' => $action->getPluginKey(),
                'userId' => $action->getUserId(),
                'countOnPage' => $this->sharedData['configs']['comments_count']
            );
        }

        $userIds = array_values($userIds);
        $this->sharedData['usersIdList'] = $userIds;

        $this->sharedData['usersInfo'] = array(
            'avatars' => array(),
            'urls' => array(),
            'names' => array(),
            'roleLabels' => array()
        );

        if ( !empty($userIds) )
        {
            $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

            foreach ( $usersInfo as $uid => $userInfo )
            {
                $this->sharedData['usersInfo']['avatars'][$uid] = $userInfo['src'];
                $this->sharedData['usersInfo']['urls'][$uid] = $userInfo['url'];
                $this->sharedData['usersInfo']['names'][$uid] = $userInfo['title'];
                $this->sharedData['usersInfo']['roleLabels'][$uid] = array(
                    'label' => $userInfo['label'],
                    'labelColor' => $userInfo['labelColor']
                );
            }
        }


        $this->sharedData['commentsData'] = BOL_CommentService::getInstance()->findBatchCommentsData($entityList);
        $this->sharedData['likesData'] = CNEWS_BOL_Service::getInstance()->findLikesByEntityList($entityList);
    }

    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    /**
     * 
     * @param CNEWS_CLASS_Action $action
     * @param array $sharedData
     * @return CNEWS_CMP_FeedItem
     */
    protected function createItem( CNEWS_CLASS_Action $action, $sharedData )
    {
        return PEEP::getClassInstance("CNEWS_CMP_FeedItem", $action, $sharedData);
    }
    
    public function tplRenderItem( $params = array() )
    {
        $action = $this->feed[$params['action']];

        $cycle = array(
            'lastItem' => $params['lastItem']
        );

        $feedItem = $this->createItem($action, $this->sharedData);
        $feedItem->setDisplayType($this->displayType);

        return $feedItem->renderMarkup($cycle);
    }

    public function render()
    {
        $out = array();
        foreach ( $this->feed as $action )
        {
            $out[] = $action->getId();
        }

        $this->assign('feed', $out);

	PEEP_ViewRenderer::getInstance()->registerFunction('cnews_item', array( $this, 'tplRenderItem' ) );
        $out = parent::render();
	PEEP_ViewRenderer::getInstance()->unregisterFunction('cnews_item');

	return $out;
    }
}