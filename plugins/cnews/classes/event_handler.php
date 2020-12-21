<?php

class CNEWS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var CNEWS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = CNEWS_BOL_Service::getInstance();
    }

    private function validateParams( $params, $requiredList, $orRequiredList = array() )
    {
        $fails = array();

        foreach ( $requiredList as $required )
        {
            if ( empty($params[$required]) )
            {
                $fails[] = $required;
            }
        }

        if ( !empty($fails) )
        {
            if ( !empty($orRequiredList) )
            {
                $this->validateParams($params, $orRequiredList);

                return;
            }

            throw new InvalidArgumentException('Next params are required: ' . implode(', ', $fails));
        }
    }

    private function extractEventParams( PEEP_Event $event )
    {
        $defaultParams = array(
            'postOnUserFeed' => true,
            'visibility' => CNEWS_BOL_Service::VISIBILITY_FULL,
            'replace' => false,
            'merge' => false
        );

        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($params['userId']) )
        {
            $params['userId'] = PEEP::getUser()->getId();
        }

        if ( isset($data['time']) )
        {
            $params['time'] = $data['time'];
        }

        if ( isset($data['params']) && is_array($data['params']) )
        {
            $params = array_merge($params, $data['params']);
        }

        return array_merge($defaultParams, $params);
    }

    private function extractEventData( PEEP_Event $event )
    {
        $data = $event->getData();
        unset($data['params']);

        return $data;
    }

    public function action( PEEP_Event $originalEvent )
    {
        $params = $this->extractEventParams($originalEvent);
        $this->validateParams($params, array('entityType', 'entityId'));

        $data = $originalEvent->getData();
        $actionDto = null;

        $mergeTo = null;
        
        if ( is_array($params['merge']) )
        {
            $actionDto = $data['actionDto'] = $this->service->findAction($params['merge']['entityType'], $params['merge']['entityId']);
            $mergeTo = $params['merge'];
        }
        else
        {
            $actionDto = $data['actionDto'] = $this->service->findAction($params['entityType'], $params['entityId']);
        }

        $event = new PEEP_Event('feed.on_entity_action', $params, $data);
        PEEP::getEventManager()->trigger($event);
        
        $params = $this->extractEventParams($event);
        $data = $this->extractEventData($event);
        
        if ( $mergeTo !== null && ( $mergeTo["entityType"] != $params['merge']["entityType"] || $mergeTo["entityId"] != $params['merge']["entityId"] ) )
        {
            $actionDto = $data['actionDto'] = $this->service->findAction($params['merge']['entityType'], $params['merge']['entityId']);
            $mergeTo = $params['merge'];
        }
        
        $actionDto = $data['actionDto'] = empty($data['actionDto']) ? $actionDto : $data['actionDto'];
        
        if ( $actionDto !== null )
        {
            $action = $actionDto;
            
            $action->entityType = $params['entityType'];
            $action->entityId = $params['entityId'];
            
            $params['pluginKey'] = empty($params['pluginKey']) ? $action->pluginKey : $params['pluginKey'];
            $actionData = json_decode($action->data, true);
            $data = array_merge($actionData, $data);

            $event = new PEEP_Event('feed.on_entity_update', $params, $data);
            PEEP::getEventManager()->trigger($event);
            
            unset($data['actionDto']);

            $params = $this->extractEventParams($event);
            $data = $this->extractEventData($event);

            if ( $params['replace'] )
            {
                $this->service->removeAction($action->entityType, $action->entityId);
                $action->id = null;
            }
            
            $action->data = json_encode($data);

            if ( empty($data["content"]) )
            {
                $action->format = CNEWS_CLASS_FormatManager::FORMAT_EMPTY;
            }
            else if ( !empty($data["content"]["format"]) )
            {
                $action->format = trim($data["content"]["format"]);
            }
            
            $this->service->saveAction($action);

            $activityParams = array(
                'pluginKey' => $params['pluginKey'],
                'entityType' => $params['entityType'],
                'entityId' => $params['entityId'],
                'activityType' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'actionId' => $action->id
            );

            if ( isset($params['visibility']) )
            {
                $activityParams['visibility'] = $params['visibility'];
            }

            if ( isset($params['time']) )
            {
                $activityParams['time'] = $params['time'];
            }

            if ( isset($params['postOnUserFeed']) )
            {
                $activityParams['postOnUserFeed'] = $params['postOnUserFeed'];
            }

            if ( !empty($params['privacy']) )
            {
                $activityParams['privacy'] = $params['privacy'];
            }

            if ( !empty( $params['feedType']) && !empty($params['feedId']) )
            {
                $activityParams['feedType'] = $params['feedType'];
                $activityParams['feedId'] = $params['feedId'];
            }

            $temp = empty($data['ownerId']) ? $params['userId'] : $data['ownerId'];
            $userIds = !is_array($temp) ? array($temp) : $temp;

            foreach ( $userIds as $userId )
            {
                $activityParams['userId'] = (int) $userId;
                $activityParams['activityId'] = (int) $userId;

                $activityEvent = new PEEP_Event('feed.activity', $activityParams);
                $this->activity($activityEvent);
            }
        }
        else
        {
            $_authorIdList = is_array($params['userId']) ? $params['userId'] : array($params['userId']);
            $authorIdList = array();
            
            foreach ( $_authorIdList as $uid )
            {
                $activityKey = "create.{$params['entityId']}:{$params['entityType']}.{$params['entityId']}:{$uid}";
                if ( $this->testActivity($activityKey) )
                {
                    $authorIdList[] = $uid;
                }
            }
            
            if ( empty($authorIdList) )
            {
                return;
            }
            
            $params["userId"] = count($authorIdList) == 1 ? $authorIdList[0] : $authorIdList;

            $event = new PEEP_Event('feed.on_entity_add', $params, $data);
            PEEP::getEventManager()->trigger($event);

            $params = $this->extractEventParams($event);
            $data = $this->extractEventData($event);

            if ( empty($data['content']) && empty($data['string']) && empty($data['line']) )
            {
                return;
            }

            if ( is_array($params['replace']) )
            {
                $this->service->removeAction($params['replace']['entityType'], $params['replace']['entityId']);
            }

            $action = new CNEWS_BOL_Action();
            $action->entityType = $params['entityType'];
            $action->entityId = $params['entityId'];
            $action->pluginKey = $params['pluginKey'];
            
            if ( empty($data["content"]) )
            {
                $action->format = CNEWS_CLASS_FormatManager::FORMAT_EMPTY;
            }
            else if ( !empty($data["content"]["format"]) )
            {
                $action->format = trim($data["content"]["format"]);
            }
            
            $action->data = json_encode($data);

            $this->service->saveAction($action);

            PEEP::getEventManager()->trigger(new PEEP_Event(CNEWS_BOL_Service::EVENT_AFTER_ACTION_ADD, array(
                "actionId" => $action->id,
                "entityType" => $action->entityType,
                "entityId" => $action->entityId
            )));

            $activityParams = array(
                'pluginKey' => $params['pluginKey'],
                'entityType' => $params['entityType'],
                'entityId' => $params['entityId'],
                'activityType' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'visibility' => (int) $params['visibility'],
                'actionId' => $action->id,
                'postOnUserFeed' => $params['postOnUserFeed'],
                'subscribe' => isset($params['subscribe']) ? (bool) $params['subscribe'] : true,
                'time' => empty($params['time']) ? time() : $params['time']
            );

            if ( !empty($params['privacy']) )
            {
                $activityParams['privacy'] = $params['privacy'];
            }

            if ( !empty( $params['feedType']) && !empty($params['feedId']) )
            {
                $activityParams['feedType'] = $params['feedType'];
                $activityParams['feedId'] = $params['feedId'];
            }

            $temp = empty($data['ownerId']) ? $params['userId'] : $data['ownerId'];
            $userIds = !is_array($temp) ? array($temp) : $temp;

            foreach ( $userIds as $userId )
            {
                $activityParams['userId'] = (int) $userId;
                $activityParams['activityId'] = (int) $userId;

                $activityEvent = new PEEP_Event('feed.activity', $activityParams);
                $this->activity($activityEvent);
            }
        }
    }

    public function activity( PEEP_Event $activityEvent )
    {
        $params = $activityEvent->getParams();
        $data = $activityEvent->getData();
        $data = empty($data) ? array() : $data;

        $this->validateParams($params,
            array('activityType', 'activityId', 'entityType', 'entityId', 'userId', 'pluginKey'),
            array('activityType', 'activityId', 'actionId', 'userId', 'pluginKey'));

        $activityKey = "{$params['activityType']}.{$params['activityId']}:{$params['entityType']}.{$params['entityId']}:{$params['userId']}";
        if ( !$this->testActivity($activityKey) )
        {
            return;
        }

        $actionId = empty($params['actionId']) ? null : $params['actionId'];

        $onEvent = new PEEP_Event('feed.on_activity', $activityEvent->getParams(), array( 'data' => $data ));
        PEEP::getEventManager()->trigger($onEvent);
        $onData = $onEvent->getData();
        $data = $onData['data'];

        if ( !empty($onData['params']) && is_array($onData['params']) )
        {
            $params = array_merge($params, $onData['params']);
        }

        if ( !in_array($params['activityType'], CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) && empty($data['string']) )
        {
            return;
        }

        if ( empty($actionId) )
        {
            $actionDto = $this->service->findAction($params['entityType'], $params['entityId']);

            if ( $actionDto === null )
            {
                $actionEvent = new PEEP_Event('feed.action', array(
                    'pluginKey' => $params['pluginKey'],
                    'userId' => $params['userId'],
                    'entityType' => $params['entityType'],
                    'entityId' => $params['entityId']
                ), array(
                    'data' => $data
                ));

                $this->action($actionEvent);
                $actionDto = $this->service->findAction($params['entityType'], $params['entityId']);
            }

            if ( $actionDto === null )
            {
                return;
            }

            $actionId = (int) $actionDto->id;
        }

        $activity = $this->service->findActivityItem($params['activityType'], $params['activityId'], $actionId);

        if ( $activity === null )
        {
            $privacy = empty($params['privacy']) ? CNEWS_BOL_Service::PRIVACY_EVERYBODY : $params['privacy'];
            $activity = new CNEWS_BOL_Activity();
            $activity->activityType = $params['activityType'];
            $activity->activityId = $params['activityId'];
            $activity->actionId = $actionId;
            $activity->privacy = $privacy;
            $activity->userId = $params['userId'];
            $activity->visibility = empty($params['visibility']) ? CNEWS_BOL_Service::VISIBILITY_FULL : $params['visibility'];
            $activity->timeStamp = empty($params['time']) ? time() : $params['time'];
            $activity->data = json_encode($data);
        }
        else
        {
            $activity->privacy = empty($params['privacy']) ? $activity->privacy : $params['privacy'];
            $activity->timeStamp = empty($params['time']) ? $activity->timeStamp : $params['time'];
            $activity->visibility = empty($params['visibility']) ? $activity->visibility : $params['visibility'];
            $_data = array_merge( json_decode($activity->data, true), $data );
            $activity->data = json_encode($_data);
        }

        $this->service->saveActivity($activity);

        if ( isset($params['subscribe']) && $params['subscribe'] )
        {
            $subscribe = new CNEWS_BOL_Activity();
            $subscribe->actionId = $actionId;
            $subscribe->userId = $params['userId'];
            $subscribe->visibility = CNEWS_BOL_Service::VISIBILITY_FULL;
            $subscribe->privacy = CNEWS_BOL_Service::PRIVACY_EVERYBODY;
            $subscribe->timeStamp = empty($params['time']) ? time() : $params['time'];
            $subscribe->activityType = CNEWS_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE;
            $subscribe->activityId = $params['userId'];
            $subscribe->data = json_encode(array());

            $this->service->saveActivity($subscribe);
        }

        if ( isset($params['subscribe']) && !$params['subscribe'] )
        {
            $this->service->removeActivity("subscribe.{$params['userId']}:$actionId");
        }

        if ( !isset($params['postOnUserFeed']) || $params['postOnUserFeed'] )
        {
            $this->service->addActivityToFeed($activity, 'user', $activity->userId);
        }

        if ( isset($params['postOnUserFeed']) && !$params['postOnUserFeed'] )
        {
            $this->service->deleteActivityFromFeed($activity->id, 'user', $activity->userId);
        }

        if ( !empty($params['feedType']) && !empty($params['feedId']) )
        {
            $this->service->addActivityToFeed($activity, $params['feedType'], $params['feedId']);
        }

        $params = $activityEvent->getParams();
        $params['actionId'] = $actionId;

        $onEvent = new PEEP_Event('feed.after_activity', $params, array( 'data' => $data ));
        PEEP::getEventManager()->trigger($onEvent);
    }

    public function afterActivity( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $this->service->clearUserFeedCahce($params['userId']);
    }

    public function onActivity( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['privacy']) )
        {
            $activityKey = "{$params['activityType']}.{$params['activityId']}:{$params['entityType']}.{$params['entityId']}:{$params['userId']}";
            $action = $this->service->getPrivacyActionByActivityKey($activityKey);

            $privacy = CNEWS_BOL_Service::PRIVACY_EVERYBODY;

            if ( !empty($action) )
            {
                $t = PEEP::getEventManager()->call('plugin.privacy.get_privacy', array(
                    'ownerId' => $params['userId'],
                    'action' => $action
                ));

                $privacy = empty($t) ? $privacy : $t;
            }

            $data['params']['privacy'] = $privacy;

            $e->setData($data);
        }
    }

    private function testActivity( $activityKey )
    {
        $disbledActivity = CNEWS_BOL_CustomizationService::getInstance()->getDisabledEntityTypes();

        if ( empty($disbledActivity) )
        {
            return true;
        }

        return !$this->service->testActivityKey($activityKey, $disbledActivity);
    }

    public function addComment( PEEP_Event $e )
    {
        $this->onCommentAdd($e);

        if ( !PEEP::getConfig()->getValue('cnews', 'allow_comments') )
        {
            return;
        }

        $params = $e->getParams();

        $eventParams = array(
            'entityType' => $params['entityType'],
            'entityId' => $params['entityId'],
            'userId' => $params['userId'],
            'pluginKey' => $params['pluginKey'],
            'commentId' => $params['commentId']
        );

        $comment = BOL_CommentService::getInstance()->findComment($params['commentId']);
        $attachment = json_decode($comment->getAttachment(), true);

        $eventData = array(
            'message' => $comment->getMessage(),
            'attachment' => $attachment
        );

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.after_comment_add', $eventParams, $eventData));
    }

    public function afterComment( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $eventParams = array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => $params['pluginKey'],
            'subscribe' => true
        );

        $eventData = array(
            'commentId' => $params['commentId']
        );

        switch ( $params['entityType'] )
        {
            case 'user-status':

                $action = CNEWS_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

                if ( empty($action) )
                {
                    return;
                }

                $actionData = json_decode($action->data, true);

                if ( empty($actionData['data']['userId']) )
                {
                    $cActivities = $this->service->findActivity( CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
                    $cActivity = reset($cActivities);

                    if ( empty($cActivity) )
                    {
                        return;
                    }

                    $userId = $cActivity->userId;
                }
                else
                {
                    $userId = $actionData['data']['userId'];
                }

                if ($userId == $params['userId'])
                {
                    $eventData['string'] = array("key" => 'cnews+activity_string_status_self_comment', "vars" => array(
                        'comment' => $data['message']
                    ));
                }
                else
                {
                    $userName = BOL_UserService::getInstance()->getDisplayName($userId);
                    $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
                    $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

                    $eventData['string'] = array("key" => 'cnews+activity_string_status_comment', "vars" => array(
                        'comment' => $data['message'],
                        'user' => $userEmbed
                    ));
                }

                break;

            default:
                return;
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', $eventParams, $eventData));
    }

    public function onCommentAdd( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != 'base_profile_wall' )
        {
            return;
        }

        $event = new PEEP_Event('feed.action', $params);
        PEEP::getEventManager()->trigger($event);
    }

    public function deleteComment( PEEP_Event $e )
    {
        $params = $e->getParams();
        $commentId = $params['commentId'];

        $event = new PEEP_Event('feed.delete_activity', array(
            'entityType' => $params['entityType'],
            'entityId' => $params['entityId'],
            'activityType' => 'comment',
            'activityId' => $commentId
        ));
        PEEP::getEventManager()->trigger($event);
    }

    public function addLike( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != 'user-status' )
        {
            return;
        }

        $data = $e->getData();

        $action = CNEWS_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = $this->service->findActivity( CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $userId = $cActivity->userId;
        }
        else
        {
            $userId = $actionData['data']['userId'];
        }

        $eventData = $data;

        if ( $userId == $params['userId'] )
        {
            $eventData['string'] = array("key" => 'cnews+activity_string_self_status_like');
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

            $eventData['string'] = array(
                "key" => "cnews+activity_string_status_like", "vars" => array(
                    'user' => $userEmbed
                )
            );
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'cnews'
        ), $eventData ));
    }

    public function removeLike( PEEP_Event $e )
    {
        $params = $e->getParams();

        $event = new PEEP_Event('feed.delete_activity', array(
            'entityType' => $params['entityType'],
            'entityId' => $params['entityId'],
            'activityType' => 'like',
            'activityId' => $params['userId']
        ));
        PEEP::getEventManager()->trigger($event);
    }

    public function removeActivity( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( isset($params['activityKey']) )
        {
            $activityKey = $params['activityKey'];
        }
        else
        {
            $keyData = array();

            foreach ( array('activityType', 'activityId', 'entityType', 'entityId', 'userId') as $item )
            {
                $keyData[$item] = empty($params[$item]) ? '*' : $params[$item];
            }

            $actionKey = empty($params['actionUniqId']) ? $keyData['entityType'] . '.' . $keyData['entityId'] : $params['actionUniqId'];
            $_activityKey = empty($params['activityUniqId']) ? $keyData['activityType'] . '.' . $keyData['activityId'] : $params['activityUniqId'];

            $activityKey = "$_activityKey:$actionKey:{$keyData['userId']}";
        }

        $this->service->removeActivity($activityKey);
    }

    public function addFollow( PEEP_Event $e )
    {
        $params = $e->getParams();

        $this->validateParams($params, array('feedType', 'feedId', 'userId'));

        if ( !empty($params["permission"]) )
        {
            $this->service->addFollow($params['userId'], $params['feedType'], $params['feedId'], $params["permission"]);
            
            return;
        }
        
        $event = new BASE_CLASS_EventCollector('feed.collect_follow_permissions', $params);
        PEEP::getEventManager()->trigger($event);

        $data = $event->getData();
        $data[] = CNEWS_BOL_Service::PRIVACY_EVERYBODY;
        
        foreach ( array_unique($data) as $permission )
        {
            $this->service->addFollow($params['userId'], $params['feedType'], $params['feedId'], $permission);
        }
    }

    public function removeFollow( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('feedType', 'feedId', 'userId'));
        
        $permission = empty($params['permission']) ? null : $params['permission'];
        $this->service->removeFollow($params['userId'], $params['feedType'], $params['feedId'], $permission);
    }

    public function isFollow( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('feedType', 'feedId', 'userId'));
        
        $permission = empty($params['permission']) ? CNEWS_BOL_Service::PRIVACY_EVERYBODY : $params['permission'];
        $result = $this->service->isFollow($params['userId'], $params['feedType'], $params['feedId'], $permission);
        $e->setData($result);

        return $result;
    }

    public function isFollowList( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('feedList', 'userId'));

        $permission = empty($params['permission']) ? null : $params['permission'];
        $result = $this->service->isFollowList($params['userId'], $params['feedList'], $permission);
        $e->setData($result);

        return $result;
    }

    public function getAllFollows( PEEP_Event $e )
    {
        $params = $e->getParams();

        $this->validateParams($params, array('feedType', 'feedId'));

        $permission = empty($params['permission']) ? null : $params['permission'];
        $list = $this->service->findFollowList($params['feedType'], $params['feedId'], $permission);
        $out = array();

        foreach ( $list as $item )
        {
            $out[] = array(
                'userId' => $item->userId,
                'permission' => $item->permission
            );
        }

        $e->setData($out);

        return $out;
    }

    public function statusUpdate( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $eventParams = array(
            'pluginKey' => 'cnews',
            'entityType' => $params['feedType'] . '-status',
            'entityId' => $data['statusId'],
            'postOnUserFeed' => false,
            'feedType' => $params['feedType'],
            'feedId' => $params['feedId']
        );

        $status = nl2br($data['status']);
        
        $content = array(
            "format" => "text",
            "vars" => array(
                "status" => $status
            )
        );
        
        $contentImage = null;

        if ( !empty($data['content']) )
        {
            $data['content'] = array_merge(array(
                "title" => null, 
                "description" => null, 
                "thumbnail_url" => null, 
                "html" => null
            ), $data['content']);
            
            $content["vars"]["title"] = $data['content']["title"];
            $content["vars"]["description"] = $data['content']["description"];
            
            $contentHref = empty($data['content']["href"]) ? null : $data['content']["href"];
            $content["vars"]["url"] = empty($data['content']["url"]) ? $contentHref : $data['content']["url"];
                        
            switch ( $data['content']["type"] )
            {
                case "photo":
                    $content["format"] = "image";
                    $content["vars"]["image"] = $data['content']["url"];
                    
                    $content["vars"]["url"] = null;
                    
                    $contentImage = $data['content']["url"];
                    break;
                
                case "video":
                    $content["format"] = "video";
                    $content["vars"]["image"] = $data['content']["thumbnail_url"];
                    $content["vars"]["embed"] = $data['content']["html"];
                    
                    $contentImage = $data['content']["thumbnail_url"];
                    break;
                
                case "link":
                    $content["format"] = "image_content";
                    $content["vars"]["image"] = $data['content']["thumbnail_url"];
                    $content["vars"]["thumbnail"] = $data['content']["thumbnail_url"];
                    
                    $contentImage = $data['content']["thumbnail_url"];
                    break;
            }
        }
        
        $eventData = array_merge($data, array(
            'content' => $content,
            'contentImage' => $contentImage,
            'view' => array(
                'iconClass' => 'peep_ic_comment'
            ),
            'data' => array(
                'userId' => $params['userId'],
                'status' => $status
            )
        ));

        if ( $params['feedType'] == 'user' && $params['feedId'] != $params['userId'] )
        {
            $eventData['context'] = array(
                'label' => BOL_UserService::getInstance()->getDisplayName($params['feedId']),
                'url' => BOL_UserService::getInstance()->getUserUrl($params['feedId'])
            );
        }

        if ( !empty($params['visibility']) )
        {
            $eventParams['visibility'] = (int) $params['visibility'];
        }

        if ( !empty($data['visibility']) )
        {
            $eventParams['visibility'] = (int) $data['visibility'];
        }

        PEEP::getEventManager()->trigger( new PEEP_Event('feed.action', $eventParams, $eventData) );
    }

    public function installWidget( PEEP_Event $e )
    {
        $params = $e->getParams();

        $widgetService = BOL_ComponentAdminService::getInstance();

        try
        {
            $widget = $widgetService->addWidget('CNEWS_CMP_EntityFeedWidget', false);
            $widgetPlace = $widgetService->addWidgetToPlace($widget, $params['place']);
            $widgetService->addWidgetToPosition($widgetPlace, $params['section'], $params['order']);
        }
        catch ( Exception $event )
        {
            $e->setData(false);
        }

        $e->setData($widgetPlace->uniqName);
    }

    public function deleteAction( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('entityType', 'entityId'));

        $this->service->removeAction($params['entityType'], $params['entityId']);
    }

    public function deleteActivity( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('entityType', 'entityId', 'activityType, activityId'));

        $this->service->findActivity($params['entityType'], $params['entityId']);
    }

    public function onPluginDeactivate( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] == 'cnews' )
        {
            return;
        }

        $this->service->setActionStatusByPluginKey($params['pluginKey'], CNEWS_BOL_Service::ACTION_STATUS_INACTIVE);
    }

    public function onPluginActivate( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] == 'cnews' )
        {
            return;
        }

        $this->service->setActionStatusByPluginKey($params['pluginKey'], CNEWS_BOL_Service::ACTION_STATUS_ACTIVE);
    }

    public function onPluginUninstall( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] == 'cnews' )
        {
            return;
        }

        $this->service->removeActionListByPluginKey($params['pluginKey']);
    }

    public function getUserStatus( PEEP_Event $e )
    {
        $params = $e->getParams();

        $event = new PEEP_Event('feed.get_status', array(
            'feedType' => 'user',
            'feedId' => $params['userId']
        ));

        $this->getStatus($event);

        $e->setData($event->getData());
    }

    public function getStatus( PEEP_Event $e )
    {
        $params = $e->getParams();

        $status = $this->service->getStatus($params['feedType'], $params['feedId']);

        $e->setData($status);
    }

    public function entityAdd( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'base_profile_wall' )
        {
            return;
        }

        $comment = BOL_CommentService::getInstance()->findComment($params['commentId']);

        $attachment = empty($comment->attachment) ? null : json_decode($comment->attachment, true);

        if ( empty($attachment) )
        {
            $data['attachment'] = null;
        }
        else
        {
            $data["attachmentId"] = empty($attachment['uid']) ? null : $attachment['uid'];
            
            $data['attachment'] = array(
                'oembed' => $attachment,
                'attachmentId' => $data["attachmentId"],

                'url' => empty($attachment['url'])
                    ? null
                    : $attachment['url']
            );
        }

        $data['content'] = '[ph:attachment]';
        $data['string'] = strip_tags($comment->getMessage());
        $data['string'] = UTIL_HtmlTag::autoLink($data['string']);
        
        $data['context'] = array(
            'label' => BOL_UserService::getInstance()->getDisplayName($params['entityId']),
            'url' => BOL_UserService::getInstance()->getUserUrl($params['entityId'])
        );

        $data['params']['feedType'] = 'user';
        $data['params']['feedId'] = $params['entityId'];

        $data['params']['entityType'] = 'user-comment';
        $data['params']['entityId'] = $params['commentId'];

        $data['view'] = array(
            'iconClass' => 'peep_ic_comment'
        );

        $data['features'] = array();

        $e->setData($data);
    }

    public function desktopItemRender( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($data['attachment']) && !empty($data['attachment']['oembed']) )
        {
            $oembed = array_filter($data['attachment']['oembed']);

            if ( !empty($oembed) )
            {
                //$canDelete = $params['createActivity']['userId'] == PEEP::getUser()->getId();
                $canDelete = false;

                $oembedCmp = new BASE_CMP_OembedAttachment($data['attachment']['oembed'], $canDelete);
                $oembedCmp->setContainerClass('cnews_attachment');
                $oembedCmp->setDeleteBtnClass('cnews_attachment_remove');
                $data['assign']['attachment'] = array('template'=>'attachment', 'vars' => array(
                    'content' => $oembedCmp->render()
                ));
            }
        }

        $e->setData($data);
    }
    
    public function genericItemRender( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( in_array($params['action']['entityType'], array('user-comment', 'user-status')) && $params['feedType'] == 'user' && $params['createActivity']->userId != $params['feedId'] )
        {
            $data['context'] = null;
        }

        $actionUserId = $userId = (int) $data['action']['userId'];
        if ( in_array($params['feedType'], array('site', 'my')) 
                && $actionUserId != PEEP::getUser()->getId() 
                && !BOL_AuthorizationService::getInstance()->isSuperModerator($actionUserId)
                && PEEP::getUser()->isAuthorized('base') )
        {
            $callbackUrl = PEEP_URL_HOME . PEEP::getRequest()->getRequestUri();

            array_unshift($data['contextMenu'], array(
                'label' => PEEP::getLanguage()->text('cnews', 'delete_feed_item_user_label'),
                'attributes' => array(
                    'onclick' => UTIL_JsGenerator::composeJsString('if ( confirm($(this).data(\'confirm-msg\')) ) PEEP.Users.deleteUser({$userId}, \'' . $callbackUrl . '\', true);', array(
                        'userId' => $actionUserId
                    )),
                    "data-confirm-msg" => PEEP::getLanguage()->text('base', 'are_you_sure')
                ),
                "class" => "peepm_red_btn"
            ));
        }

        $isFeedOwner = $params['feedType'] == "user" && $params["feedId"] == PEEP::getUser()->getId();
        $isStatus = in_array($params['action']['entityType'], array('user-comment', 'user-status'));
        
        $canRemove = PEEP::getUser()->isAuthenticated()
                && ( 
                    $params['action']['userId'] == PEEP::getUser()->getId() 
                    || PEEP::getUser()->isAuthorized('cnews')
                    || ( $isFeedOwner && $isStatus && $params['action']['onOriginalFeed'] )
                );
        
        if ( $canRemove && in_array($params['feedType'], array('site', 'my', 'user')) )
        {
            array_unshift($data['contextMenu'], array(
                'label' => PEEP::getLanguage()->text('cnews', 'feed_delete_item_label'),
                'attributes' => array(
                    'data-confirm-msg' => PEEP::getLanguage()->text('base', 'are_you_sure')
                ),
                "class" => "cnews_remove_btn peepm_red_btn"
            ));
        }
        
        $e->setData($data);
    }
    
    public function feedItemRenderFlagBtn( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $userId = PEEP::getUser()->getId();
        
        if ( empty($userId) || $params['action']['userId'] == $userId )
        {
            return;
        }
        
        $contentType = BOL_ContentService::getInstance()->getContentTypeByEntityType($params['action']['entityType']);
        $flagsAllowed = !empty($contentType) && in_array(BOL_ContentService::MODERATION_TOOL_FLAG, $contentType["moderation"]);
        
        if ( !$flagsAllowed )
        {
            return;
        }
        
        array_unshift($data['contextMenu'], array(
            'label' => PEEP::getLanguage()->text('base', 'flag'),
            'attributes' => array(
                'onclick' => 'PEEP.flagContent($(this).data().etype, $(this).data().eid)',
                "data-etype" => $params['action']['entityType'],
                "data-eid" => $params['action']['entityId']
            )
        ));
        
        $e->setData($data);
    }
    
    public function onFeedItemRenderContext( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( empty($data['contextFeedType']) || $data['contextFeedType'] == $params['feedType'] )
        {
            return;
        }
        
        if ( $data['contextFeedType'] != "user" )
        {
            return;
        }
        
        $userId = (int)$data['contextFeedId'];
        
        $data['context'] = array(
            'label' => BOL_UserService::getInstance()->getDisplayName($userId),
            'url' => BOL_UserService::getInstance()->getUserUrl($userId)
        );

        $event->setData($data);
    }

    public function userUnregister( PEEP_Event $e )
    {
        $params = $e->getParams();

        if ( !isset($params['deleteContent']) || !$params['deleteContent'] )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $actions = $this->service->findActionsByUserId($userId);

        foreach ( $actions as $action )
        {
            $this->service->removeAction($action->entityType, $action->entityId);
        }

        $this->service->removeLikesByUserId($userId);
        $this->service->removeActivityByUserId($userId);
    }

    public function onPrivacyChange( PEEP_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        $privacyList = array();

        foreach ( $actionList as $action => $privacy )
        {
            $a = $this->service->getActivityKeysByPrivacyAction($action);
            foreach ( $a as $item )
            {
                $privacyList[$privacy][] = $item;
            }
        }

        foreach ( $privacyList as $privacy => $activityKeys )
        {
            $key = implode(',', array_filter($activityKeys));
            $this->service->setActivityPrivacy($key, $privacy, $userId);
        }
    }

    public function afterAppInit()
    {
        $this->service->collectPrivacy();
    }

    public function clearCache( PEEP_Event $e )
    {
        $params = $e->getParams();
        $this->validateParams($params, array('userId'));

        $this->service->clearUserFeedCahce($params['userId']);
    }

    public function userBlocked( PEEP_Event $e )
    {
        $params = $e->getParams();

        $event = new PEEP_Event('feed.remove_follow', array(
            'feedType' => 'user',
            'feedId' => $params['userId'],
            'userId' => $params['blockedUserId']
        ));
        PEEP::getEventManager()->trigger($event);

        $event = new PEEP_Event('feed.remove_follow', array(
            'feedType' => 'user',
            'feedId' => $params['blockedUserId'],
            'userId' => $params['userId']
        ));
        PEEP::getEventManager()->trigger($event);
    }

    public function onCommentNotification( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user-status' )
        {
            return;
        }

        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $userService = BOL_UserService::getInstance();

        $action = CNEWS_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);
        $status = empty($actionData['data']['status'])
            ? empty($actionData['string']) ? null : $actionData['string']
            : $actionData['data']['status'];

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = $this->service->findActivity( CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $ownerId = $cActivity->userId;
        }
        else
        {
            $ownerId = $actionData['data']['userId'];
        }

        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        
        $contentImage = null;
        
        if ( !empty($comment->attachment) )
        {
            $attachment = json_decode($comment->attachment, true);
            
            if ( !empty($attachment["thumbnail_url"]) )
            {
                $contentImage = $attachment["thumbnail_url"];
            }
            if ( $attachment["type"] == "photo" )
            {
                $contentImage = $attachment["url"];
            }
        }

        $url = PEEP::getRouter()->urlForRoute('cnews_view_item', array('actionId' => $action->id));

        if ( $ownerId != $userId )
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, true, false);

            $stringKey = empty($status)
                ? 'cnews+email_notifications_empty_status_comment'
                : 'cnews+email_notifications_status_comment';

            $event = new PEEP_Event('notifications.add', array(
                'pluginKey' => 'cnews',
                'entityType' => 'status_comment',
                'entityId' => $commentId,
                'userId' => $ownerId,
                'action' => 'cnews-status_comment'
            ), array(
                'format' => "text",
                'avatar' => $avatar[$userId],
                'string' => array(
                    'key' => $stringKey,
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'status' => UTIL_String::truncate($status, 100, '...'),
                        'url' => $url
                    )
                ),
                'content' => $comment->getMessage(),
                'contentImage' => $contentImage,
                'url' => $url
            ));

            PEEP::getEventManager()->trigger($event);
        }
    }

    public function onLikeNotification( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'user-status' )
        {
            return;
        }

        $userId = $params['userId'];
        $userService = BOL_UserService::getInstance();

        $action = CNEWS_BOL_Service::getInstance()->findAction($params['entityType'], $params['entityId']);

        if ( empty($action) )
        {
            return;
        }

        $actionData = json_decode($action->data, true);
        $status = empty($actionData['data']['status'])
            ? $actionData['string']
            : empty($actionData['data']['status']) ? null : $actionData['data']['status'];
        
        $contentImage = empty($actionData['contentImage']) ? null : $actionData['contentImage'];

        if ( empty($actionData['data']['userId']) )
        {
            $cActivities = $this->service->findActivity( CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $action->id);
            $cActivity = reset($cActivities);

            if ( empty($cActivity) )
            {
                return;
            }

            $ownerId = $cActivity->userId;
        }
        else
        {
            $ownerId = $actionData['data']['userId'];
        }

        $url = PEEP::getRouter()->urlForRoute('cnews_view_item', array('actionId' => $action->id));

        if ( $ownerId != $userId )
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, true, false);

            $stringKey = empty($status)
                ? 'cnews+email_notifications_empty_status_like'
                : 'cnews+email_notifications_status_like';

            $event = new PEEP_Event('notifications.add', array(
                'pluginKey' => 'cnews',
                'action' => 'cnews-status_like',
                'entityType' => 'status_like',
                'entityId' => $data['likeId'],
                'userId' => $ownerId,
                'action' => 'cnews-status_like'
            ), array(
                'format' => "text",
                'avatar' => $avatar[$userId],
                'string' => array(
                    'key' => $stringKey,
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'status' => UTIL_String::truncate($status, 100, '...'),
                        'url' => $url
                    )
                ),
                'url' => $url,
                "contentImage" => $contentImage
            ));

            PEEP::getEventManager()->trigger($event);
        }
    }


    public function userFeedStatusUpdate( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['feedType'] != 'user' )
        {
            return;
        }

        $recipientId = (int) $params['feedId'];
        $userId = (int) $params['userId'];

        if ( $recipientId == $userId )
        {
            return;
        }

        $action = CNEWS_BOL_Service::getInstance()->findAction('user-status', $data['statusId']);

        if ( empty($action) )
        {
            return;
        }

        $url = PEEP::getRouter()->urlForRoute('cnews_view_item', array('actionId' => $action->id));
        $actionData = json_decode($action->data, true);
        $contentImage = empty($actionData['contentImage']) ? null : $actionData['contentImage'];
        
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, true, false);
        $avatar = $avatarData[$userId];

        $stringKey = 'cnews+notifications_user_status';

        $event = new PEEP_Event('notifications.add', array(
            'pluginKey' => 'cnews',
            'action' => 'cnews-user_status',
            'entityType' => 'user_status',
            'entityId' => $data['statusId'],
            'userId' => $recipientId
        ), array(
            'format' => "text",
            'avatar' => $avatar,
            'string' => array(
                'key' => $stringKey,
                'vars' => array(
                    'userName' => $avatar['title'],
                    'userUrl' => $avatar['url']
                )
            ),
            'content' => UTIL_String::truncate($data['status'], 100, '...'),
            'url' => $url,
            "contentImage" => $contentImage
        ));

        PEEP::getEventManager()->trigger($event);
    }



    public function collectNotificationActions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            'section' => 'cnews',
            'action' => 'cnews-status_comment',
            'sectionIcon' => 'peep_ic_clock',
            'sectionLabel' => PEEP::getLanguage()->text('cnews', 'email_notifications_section_label'),
            'description' => PEEP::getLanguage()->text('cnews', 'email_notifications_setting_status_comment'),
            'selected' => true
        ));

        $event->add(array(
            'section' => 'cnews',
            'action' => 'cnews-status_like',
            'sectionIcon' => 'peep_ic_clock',
            'sectionLabel' => PEEP::getLanguage()->text('cnews', 'email_notifications_section_label'),
            'description' => PEEP::getLanguage()->text('cnews', 'email_notifications_setting_status_like'),
            'selected' => true
        ));

        $event->add(array(
            'section' => 'cnews',
            'action' => 'cnews-user_status',
            'sectionIcon' => 'peep_ic_clock',
            'sectionLabel' => PEEP::getLanguage()->text('cnews', 'email_notifications_section_label'),
            'description' => PEEP::getLanguage()->text('cnews', 'email_notifications_setting_user_status'),
            'selected' => true
        ));
    }

    public function getActionPermalink( PEEP_Event $event )
    {
        $params = $event->getParams();
        $actionId = empty($params['actionId']) ? null : $params['actionId'];

        if ( empty($actionId) && !empty($params['entityType']) && !empty($params['entityId']) )
        {
            $action = $this->service->findAction($params['entityType'], $params['entityId']);
            if ( empty($action) )
            {
                return null;
            }

            $actionId = $action->id;
        }

        if ( empty($actionId) )
        {
            return null;
        }

        $url = $this->service->getActionPermalink($actionId);
        $event->setData($url);

        return $url;
    }
    
    public function onCollectProfileActions( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userId = $params['userId'];

        if ( !PEEP::getUser()->isAuthenticated() || PEEP::getUser()->getId() == $userId )
        {
            return;
        }

        $urlParams = array(
            'userId' => $userId,
            'backUri' => PEEP::getRouter()->getUri()
        );

        $linkId = 'follow' . rand(10, 1000000);

        $isFollowing = CNEWS_BOL_Service::getInstance()->isFollow(PEEP::getUser()->getId(), 'user', $userId);
        $followUrl = PEEP::getRouter()->urlFor('CNEWS_CTRL_Feed', 'follow');
        $followUrl = PEEP::getRequest()->buildUrlQueryString($followUrl, $urlParams);
        $followLabel = PEEP::getLanguage()->text('cnews', 'follow_button');

        $unfollowUrl = PEEP::getRouter()->urlFor('CNEWS_CTRL_Feed', 'unFollow');
        $unfollowUrl = PEEP::getRequest()->buildUrlQueryString($unfollowUrl, $urlParams);
        $unfollowLabel = PEEP::getLanguage()->text('cnews', 'unfollow_button');

        $script = UTIL_JsGenerator::composeJsString('
            var isFollowing = {$isFollowing};

            $("#' . $linkId . '").click(function()
            {
                if ( !isFollowing && {$isBlocked} )
                {
                    PEEP.error({$blockError});
                    return;
                }

                $.getJSON(isFollowing ? {$unfollowUrl} : {$followUrl}, function( r ) {
                    PEEP.info(r.message);
                });

                isFollowing = !isFollowing;
                $(this).text(isFollowing ? {$unfollowLabel} : {$followLabel})
            });

        ', array(
            'isFollowing' => $isFollowing,
            'unfollowUrl' => $unfollowUrl,
            'followUrl' => $followUrl,
            'followLabel' => $followLabel,
            'unfollowLabel' => $unfollowLabel,
            'isBlocked' => BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $userId),
            'blockError' => PEEP::getLanguage()->text('base', 'user_block_message')
        ));

        
    }
    
    function isFeedInited()
    {
        return true;
    }
    
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $event->add(
            array(
                'cnews' => array(
                    'label' => $language->text('cnews', 'auth_group_label'),
                    'actions' => array(
                        'add_comment' => $language->text('cnews', 'auth_action_label_add_comment'),
                        'allow_status_update' => $language->text('cnews', 'auth_action_label_allow_status_update')
                    )
                )
            )
        );
    }
    
    public function onPrivacyCollectActions( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $action = array(
            'key' => CNEWS_BOL_Service::PRIVACY_ACTION_VIEW_MY_FEED,
            'pluginKey' => 'cnews',
            'label' => $language->text('cnews', 'privacy_action_view_my_feed'),
            'description' => '',
            'defaultValue' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'sortOrder' => 1001
        );

        $event->add($action);
    }
    
    public function genericInit()
    {
        $eventHandler = $this;
        
        PEEP::getEventManager()->bind('feed.action', array($eventHandler, 'action'));
        PEEP::getEventManager()->bind('feed.activity', array($eventHandler, 'activity'));
        PEEP::getEventManager()->bind('feed.delete_activity', array($eventHandler, 'removeActivity'));
        PEEP::getEventManager()->bind('feed.get_all_follows', array($eventHandler, 'getAllFollows'));
        PEEP::getEventManager()->bind('feed.install_widget', array($eventHandler, 'installWidget'));
        PEEP::getEventManager()->bind('feed.delete_item', array($eventHandler, 'deleteAction'));
        PEEP::getEventManager()->bind('feed.get_status', array($eventHandler, 'getStatus'));
        PEEP::getEventManager()->bind('feed.remove_follow', array($eventHandler, 'removeFollow'));
        PEEP::getEventManager()->bind('feed.is_follow', array($eventHandler, 'isFollow'));
        PEEP::getEventManager()->bind('feed.after_status_update', array($eventHandler, 'statusUpdate'));
        PEEP::getEventManager()->bind('feed.after_status_update', array($eventHandler, 'userFeedStatusUpdate'));
        PEEP::getEventManager()->bind('feed.after_like_added', array($eventHandler, 'addLike'));
        PEEP::getEventManager()->bind('feed.after_like_removed', array($eventHandler, 'removeLike'));
        PEEP::getEventManager()->bind('feed.add_follow', array($eventHandler, 'addFollow'));
        PEEP::getEventManager()->bind('feed.on_entity_add', array($eventHandler, 'entityAdd'));
        PEEP::getEventManager()->bind('feed.on_activity', array($eventHandler, 'onActivity'));
        PEEP::getEventManager()->bind('feed.after_activity', array($eventHandler, 'afterActivity'));
        PEEP::getEventManager()->bind('feed.get_item_permalink', array($eventHandler, 'getActionPermalink'));
        PEEP::getEventManager()->bind('feed.clear_cache', array($eventHandler, 'deleteActionSet'));
        PEEP::getEventManager()->bind('feed.after_comment_add', array($eventHandler, 'afterComment'));
        PEEP::getEventManager()->bind('feed.is_inited', array($eventHandler, 'isFeedInited'));
        PEEP::getEventManager()->bind('admin.add_auth_labels', array($eventHandler, 'onCollectAuthLabels'));
        PEEP::getEventManager()->bind('plugin.privacy.get_action_list', array($eventHandler, 'onPrivacyCollectActions'));
        PEEP::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($eventHandler, 'onPrivacyChange'));
        PEEP::getEventManager()->bind('base_add_comment', array($eventHandler, 'addComment'));
        PEEP::getEventManager()->bind('base_delete_comment', array($eventHandler, 'deleteComment'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($eventHandler, 'userUnregister'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_BLOCK, array($eventHandler, 'userBlocked'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($eventHandler, 'afterAppInit'));
        //PEEP::getEventManager()->bind('base.on_get_user_status', array($eventHandler, 'getUserStatus'));
        PEEP::getEventManager()->bind('base_add_comment', array($eventHandler, 'onCommentNotification'));
        PEEP::getEventManager()->bind('feed.after_like_added', array($eventHandler, 'onLikeNotification'));
        PEEP::getEventManager()->bind('notifications.collect_actions', array($eventHandler, 'collectNotificationActions'));
        PEEP::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'genericItemRender'));
        
        PEEP::getEventManager()->bind('feed.on_item_render', array($eventHandler, 'onFeedItemRenderContext'));
        
        $credits = new CNEWS_CLASS_Credits();
        PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        
        $onceInited = PEEP::getConfig()->getValue('cnews', 'is_once_initialized');
        if ( $onceInited === null )
        {
            if ( PEEP::getConfig()->configExists('cnews', 'is_once_initialized') )
            {
                PEEP::getConfig()->saveConfig('cnews', 'is_once_initialized', 1);
            }
            else
            {
                PEEP::getConfig()->addConfig('cnews', 'is_once_initialized', 1);
            }

            $event = new PEEP_Event('feed.after_first_init', array('pluginKey' => 'cnews'));
            PEEP::getEventManager()->trigger($event);
        }
        
        CNEWS_CLASS_ContentProvider::getInstance()->init();
    }


}