<?php

class CNEWS_CMP_FeedItem extends PEEP_Component
{
    /**
     *
     * @var CNEWS_CLASS_Action
     */
    protected $action;
    protected $autoId;
    protected $displayType;

    protected $remove = false;

    protected $sharedData = array();

    public function __construct( CNEWS_CLASS_Action $action, $sharedData )
    {
        parent::__construct();

        $this->displayType = CNEWS_CMP_Feed::DISPLAY_TYPE_ACTION;
        $this->action = $action;
        $this->sharedData = $sharedData;

        $this->autoId = 'action-' . $this->sharedData['feedAutoId'] . '-' . $action->getId();
    }

    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    protected function mergeData( $data, CNEWS_CLASS_Action $_action )
    {
        $data = empty($data) ? array() : $data;

        $action = array(
            'userId' => $_action->getUserId(),
            'createTime' => $_action->getCreateTime(),
            'entityType' => $_action->getEntity()->type,
            'entityId' => $_action->getEntity()->id,
            'pluginKey' => $_action->getPluginKey(),
            'format' => $_action->getFormat()
        );

        $view = array( 'iconClass' => 'peep_ic_info', 'class' => '', 'style' => '' );
        $defaults = array(
            'line' => null, 'string' => null, 'content' => null, 'toolbar' => array(), 'context' => array(),
            'features' => array( 'comments', 'likes' ), 'contextMenu' => array()
        );

        foreach ( $defaults as $key => $value )
        {
            if ( !isset($data[$key]) )
            {
                $data[$key] = $value;
            }
        }
        
        if ( !isset($data['view']) || !is_array($data['view']) )
        {
            $data['view'] = array();
        }

        $data['view'] = array_merge($view, $data['view']);

        if ( !isset($data['action']) || !is_array($data['action']) )
        {
            $data['action'] = array();
        }

        $data['action'] = array_merge($action, $data['action']);
        
        $data['action']["userIds"] = empty($data['action']["userIds"]) 
                ? array($data['action']["userId"])
                : $data['action']["userIds"];

        return $data;
    }

    protected function getActionData( CNEWS_CLASS_Action $action )
    {
        $activity = array();
        $createActivity = $action->getCreateActivity();
        $lastActivity = null;

        foreach ( $action->getActivityList() as $a )
        {
            /* @var $a CNEWS_BOL_Activity */
            $activity[$a->id] = array(
                'activityType' => $a->activityType,
                'activityId' => $a->activityId,
                'id' => $a->id,
                'data' => json_decode($a->data, true),
                'timeStamp' => $a->timeStamp,
                'privacy' => $a->privacy,
                'userId' => $a->userId,
                'visibility' =>$a->visibility
            );

            if ( $lastActivity === null && !in_array($activity[$a->id]['activityType'], CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) )
            {
                $lastActivity = $activity[$a->id];
            }
        }

        $creatorIdList = $action->getCreatorIdList();
        $data = $this->mergeData($action->getData(), $action);

        $sameFeed = false;
        $feedList = array();
        foreach ( $action->getFeedList() as $feed )
        {
            if ( !$sameFeed )
            {
                $sameFeed = $this->sharedData['feedType'] == $feed->feedType
                        && $this->sharedData['feedId'] == $feed->feedId;
            }
            
            $feedList[] = array(
                "feedType" => $feed->feedType,
                "feedId" => $feed->id
            );
        }
        
        $eventParams = array(
            'action' => array(
                'id' => $action->getId(),
                'entityType' => $action->getEntity()->type,
                'entityId' => $action->getEntity()->id,
                'pluginKey' => $action->getPluginKey(),
                'createTime' => $action->getCreateTime(),
                'userId' => $action->getUserId(), // backward compatibility with desktop version
                "userIds" => $creatorIdList,
                'format' => $action->getFormat(),
                'data' => $data,
                "feeds" => $feedList,
                "onOriginalFeed" => $sameFeed
            ),

            'activity' => $activity,
            'createActivity' => $createActivity,
            'lastActivity' => $lastActivity,
            'feedType' => $this->sharedData['feedType'],
            'feedId' => $this->sharedData['feedId'],
            'feedAutoId' => $this->sharedData['feedAutoId'],
            'autoId' => $this->autoId
        );

        $data['action'] = array(
            'userId' => $action->getUserId(), // backward compatibility with desktop version
            "userIds" => $creatorIdList,
            'createTime' => $action->getCreateTime()
        );
 
        $shouldExtend = $this->displayType == CNEWS_CMP_Feed::DISPLAY_TYPE_ACTIVITY && $lastActivity !== null;
 
        if ( $shouldExtend )
        {
            if ( !empty($lastActivity['data']['string']) || !empty($lastActivity['data']['line']) )
            {
                $data = $this->applyRespond($data, $lastActivity);
            }
        }
        
        if ( $lastActivity !== null )
        {
            $data = $this->extendAction($data, $lastActivity);
            $data = $this->extendActionData($data, $lastActivity);
        }
 
        $event = new PEEP_Event('feed.on_item_render', $eventParams, $data);
        PEEP::getEventManager()->trigger($event);
 
        return $this->mergeData( $event->getData(), $action );
    }
    
    protected function applyRespond( $data, $respondActivity )
    {
        $data['action'] = array(
            'userId' => $respondActivity['userId'],
            'userIds' => empty($respondActivity['userIds']) ? array($respondActivity['userId']) : $respondActivity['userIds'], // backward compatibility with desktop version
            'createTime' => empty($respondActivity["data"]['timeStamp'])
                ? $respondActivity['timeStamp']
                : $respondActivity["data"]['timeStamp']
        );
        
        if ( isset($respondActivity["data"]["string"]) )
        {
            $data["string"] = $respondActivity["data"]["string"];
        }
        
        if ( isset($respondActivity["data"]["line"]) )
        {
            $data["line"] = $respondActivity["data"]["line"];
        }
        
        return $data;
    }
    
    protected function extendAction( $data, $activity )
    {
        $actionOverride = $activity['data'];
        $action = empty($actionOverride['action']) ? array() : $actionOverride['action'];
        
        if ( !empty($actionOverride['params']) )
        {
            $action = $actionOverride['params'];
        }
                
        if ( !empty($action["userId"]) && empty($action["userIds"]) )
        {
            $action["userIds"] = array($action["userId"]); // backward compatibility with desktop version
        }
        
        $data["action"] = array_merge($data["action"], $action);
        
        return $data;
   }
    
    protected function extendActionData( $data, $activity )
    {
        $actionOverride = $activity['data'];
        
        foreach ( $actionOverride as $key => $value )
        {
            if ( $key == 'view' )
            {
                if ( is_array($value) )
                {
                    $data[$key] = array_merge($data[$key], $value);
                }
            }
            else if ( $key == 'content' && is_array($value) )
            {
                $newContent = array_merge($data["key"], $value);
                
                if ( isset($value["vars"]) )
                {
                    $newContent["vars"] = array_merge($data[$key]["vars"], $value["vars"]);
                }
            }                
            else if ( !in_array($key, array("action", "string", "line")) )
            {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
 
    public function generateJs( $data )
    {
        $js = UTIL_JsGenerator::composeJsString('
            window.peep_cnews_feed_list[{$feedAutoId}].actions[{$uniq}] = new CNEWS_FeedItem({$autoId}, window.peep_cnews_feed_list[{$feedAutoId}]);
            window.peep_cnews_feed_list[{$feedAutoId}].actions[{$uniq}].construct({$data});
        ', array(
            'uniq' => $data['entityType'] . '.' . $data['entityId'],
            'feedAutoId' => $this->sharedData['feedAutoId'],
            'autoId' => $this->autoId,
            'id' => $this->action->getId(),
            'data' => array(
                'entityType' => $data['entityType'],
                'entityId' => $data['entityId'],
                'id' => $data['id'],
                'updateStamp' => $this->action->getUpdateTime(),
                'likes' => !empty($data['features']['system']['likes']) ? $data['features']['system']['likes']['count'] : 0,
                'comments' => !empty($data['features']['system']['comments']) ? $data['features']['system']['comments']['count'] : 0,
                'cycle' => $data['cycle'],
                'displayType' => $this->displayType
            )
        ));
 
        PEEP::getDocument()->addOnloadScript($js, 50);
    }
 
    protected function processAssigns( $content, $assigns )
    {
        $search = array();
        $values = array();
 
        foreach ( $assigns as $key => $item )
        {
            $search[] = '[ph:' . $key . ']';
            $values[] = $item;
        }
 
        $result = str_replace($search, $values, $content);
        $result = preg_replace('/\[ph\:\w+\]/', '', $result);
 
        return $result;
    }
 
    protected function renderTemplate( $tplFile, $vars )
    {
        $template = new CNEWS_CMP_Template();
        $template->setTemplate($tplFile);
 
        foreach ( $vars as $k => $v )
        {
            $template->assign($k, $v);
        }
 
        return $template->render();
    }
    
    protected function renderFormat( $format, $vars )
    {
        return CNEWS_CLASS_FormatManager::getInstance()->renderFormat($format, $vars);
    }
    
    protected function renderContent( $content )
    {
        if ( !is_array($content) )
        {
            return $content;
        }
 
        $vars = empty($content['vars']) || !is_array($content['vars']) ? array() : $content['vars'];
        
        $template = null;
        
        if ( !empty($content['templateFile']) )
        {
            $template = $content['templateFile'];
        }
        else if ( !empty($content['template']) )
        {
            $template = PEEP::getPluginManager()->getPlugin('cnews')->getViewDir() . 'templates' . DS . trim($content['template']) . '.html';
        }
        
        if ( $template !== null )
        {
            return $this->renderTemplate($template, $vars);
        }
 
        if ( empty($content["format"]) )
        {
            return "";
        }
        
        return $this->renderFormat($content["format"], $vars);
    }
 
    protected function getUserInfo( $userId )
    {
        $usersInfo = $this->sharedData['usersInfo'];
 
        if ( !in_array($userId, $this->sharedData['usersIdList']) )
        {
            $userInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
 
            $usersInfo['avatars'][$userId] = $userInfo[$userId]['src'];
            $usersInfo['urls'][$userId] = $userInfo[$userId]['url'];
            $usersInfo['names'][$userId] = $userInfo[$userId]['title'];
            $usersInfo['roleLabels'][$userId] = array(
                'label' => $userInfo[$userId]['label'],
                'labelColor' => $userInfo[$userId]['labelColor']
            );
        }
 
        $user = array(
            'id' => $userId,
            'avatarUrl' => $usersInfo['avatars'][$userId],
            'url' => $usersInfo['urls'][$userId],
            'name' => $usersInfo['names'][$userId],
            'roleLabel' => empty($usersInfo['roleLabels'][$userId])
                ? array('label' => '', 'labelColor' => '')
                : $usersInfo['roleLabels'][$userId]
        );
 
        return $user;
    }
    
    protected function getActionUsersInfo( $data )
    {
        $userIds = $data['action']['userIds'];
        
        if ( !empty($data['action']['avatars']) )
        {
            return array($data['action']['avatars']);
        }
        
        if ( !empty($data['action']['avatar']) )
        {
            return array($data['action']['avatar']);
        }
        
        $out = array();
 
        foreach ( $userIds as $userId )
        {
            $out[$userId] = $this->getUserInfo($userId);
        }
        
        return $out;
    }
 
    protected function getContextMenu( $data )
    {
        $contextActionMenu = new BASE_CMP_ContextAction();
 
        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('cnews_context_menu_' . $this->autoId);
        $contextParentAction->setClass('peep_cnews_context');
        $contextActionMenu->addAction($contextParentAction);
 
        $order = 1;
        foreach( $data['contextMenu'] as $action )
        {
            $action = array_merge(array(
                'label' => null,
                'order' => $order,
                'class' => null,
                'url' => null,
                'id' => null,
                'key' => uniqid($this->autoId . '_'),
                'attributes' => array()
            ), $action);
 
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
 
            $contextAction->setLabel($action['label']);
            $contextAction->setClass($action['class']);
            $contextAction->setUrl($action['url']);
            $contextAction->setId($action['id']);
            $contextAction->setKey($action['key']);
            $contextAction->setOrder($action['order']);
 
            foreach ( $action['attributes'] as $key => $value )
            {
                $contextAction->addAttribute($key, $value);
            }
 
            $contextActionMenu->addAction($contextAction);
            $order++;
        }
 
        return $contextActionMenu->render();
    }
 
    protected function getFeaturesData( $data )
    {
        $configs = $this->sharedData['configs'];
 
        $customFeatures = array();
        $systemFeatures = array();
        foreach ( $data['features'] as $key => $feature )
        {
            if ( is_string($feature) )
            {
                $systemFeatures[$feature] = array();
            }
            else if ( in_array($key, array('comments', 'likes'), true) )
            {
                $systemFeatures[$key] = $feature;
            }
            else if ( is_array($feature) )
            {
                $customFeatures[$key] = $feature;
            }
        }
        
        $features = array();
        
        if ( $configs['allow_comments'] && key_exists('comments', $systemFeatures) )
        {
            $commentsFeature = array();
            
            $featureData = $systemFeatures['comments'];
 
            $commentsFeature["authGroup"] = empty($featureData['pluginKey']) ? $data['action']['pluginKey'] : $featureData['pluginKey'];
            $commentsFeature["entityType"] = empty($featureData['entityType']) ? $data['action']['entityType'] : $featureData['entityType'];
            $commentsFeature["entityId"] = empty($featureData['entityId']) ? $data['action']['entityId'] : $featureData['entityId'];
 
            $authActionDto = BOL_AuthorizationService::getInstance()->findAction($commentsFeature["authGroup"], 'add_comment', true);
 
            if ( $authActionDto === null )
            {
                $commentsFeature["authGroup"] = 'cnews';
            }
 
            $commentsFeature['count'] = $this->sharedData['commentsData'][$commentsFeature["entityType"]][$commentsFeature["entityId"]]['commentsCount'];
            $commentsFeature['allow'] = PEEP::getUser()->isAuthorized($commentsFeature["authGroup"], 'add_comment');
            $commentsFeature['expanded'] = $configs['features_expanded'] && $commentsFeature['count'] > 0;
            $commentsFeature["comments"] = $this->sharedData['commentsData'];
            
            $features["comments"] = $commentsFeature;
        }
        
        if ( $configs['allow_likes'] && key_exists('likes', $systemFeatures) )
        {
           $likesFeature = array();
            
           $featureData = $systemFeatures['likes'];
 
           $likesFeature["entityType"] = empty($featureData['entityType']) ? $data['action']['entityType'] : $featureData['entityType'];
           $likesFeature["entityId"] = empty($featureData['entityId']) ? $data['action']['entityId'] : $featureData['entityId'];
 
           $likesData = $this->sharedData['likesData'];
           $likes = empty($likesData[$likesFeature["entityType"]][$likesFeature["entityId"]])
                ? array() : $likesData[$likesFeature["entityType"]][$likesFeature["entityId"]];
 
           $userLiked = false;
           foreach ( $likes as $like )
           {
                if ( $like->userId == PEEP::getUser()->getId() )
                {
                    $userLiked = true;
                }
           }
 
           $likesFeature['count'] = count($likes);
           $likesFeature['liked'] = $userLiked;
           $likesFeature["likes"] = $likes;
           $likesFeature['allow'] = true;
 
           if ( empty($featureData['error']) )
           {
                $likesFeature['error'] = PEEP::getUser()->isAuthenticated()
                    ? null
                    : PEEP::getLanguage()->text('cnews', 'guest_like_error');
           }
           else
           {
               $likesFeature['error'] = $featureData['error'];
           }
           
           $features["likes"] = $likesFeature;
        }
        
        return array(
            "system" => $features,
            "custom" => $customFeatures
        );
    }
    
    protected function getFeatures( $data )
    {
        $configs = $this->sharedData['configs'];
        
        $featuresData = $this->getFeaturesData($data);
        
        $out = array(
            'system' => array(
                'comments' => false,
                'likes' => false
            ),
            'custom' => array()
        );
 
        $out['custom'] = $featuresData["custom"];
        $systemFeatures = $featuresData["system"];
        
        if ( !empty($systemFeatures["comments"]) )
        {
            $feature = $systemFeatures["comments"];
            
            $commentsParams = new BASE_CommentsParams($feature["authGroup"], $feature["entityType"]);
            $commentsParams->setEntityId($feature["entityId"]);
            $commentsParams->setInitialCommentsCount($configs['comments_count']);
            $commentsParams->setLoadMoreCount(6);
            $commentsParams->setBatchData($feature["comments"]);
            
            $commentsParams->setOwnerId($this->action->getUserId());
            $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);                        
            $commentsParams->setWrapInBox(false);
            $commentsParams->setShowEmptyList(false);            
 
            if ( !empty($feature['error']) )
            {
                $commentsParams->setErrorMessage($feature['error']);
            }
 
            if ( isset($feature['allow']) )
            {
                $commentsParams->setAddComment($feature['allow']);
            }
 
            $commentCmp = new BASE_CMP_Comments($commentsParams);
            $out['system']['comments']['cmp'] = $commentCmp->render();
 
            $out['system']['comments']['count'] = $feature["count"];
            $out['system']['comments']['allow'] = $feature["allow"];
            $out['system']['comments']['expanded'] = $feature["expanded"];
        }
        
        if ( !empty($systemFeatures["likes"]) )
        {
           $feature = $systemFeatures['likes'];
 
           $out['system']['likes']['count'] = $feature["count"];
           $out['system']['likes']['liked'] = $feature["liked"];
           $out['system']['likes']['allow'] = $feature["allow"];
           $out['system']['likes']['error'] = $feature["error"];
                   
           $likeCmp = new CNEWS_CMP_Likes($feature["entityType"], $feature["entityId"], $feature["likes"]);
           $out['system']['likes']['cmp'] = $likeCmp->render();
        }
 
        return $out;
    }
    
    protected function getLocalizedText( $textData )
    {
        if ( !is_array($textData) )
        {
            return $textData;
        }
 
        $keyData = explode("+", $textData["key"]);
        $vars = empty($textData["vars"]) ? array() : $textData["vars"];
        
        return PEEP::getLanguage()->text($keyData[0], $keyData[1], $vars);
    }
 
    public function getTplData( $cycle = null )
    {
        $action = $this->action;
        $data = $this->getActionData($action);
        
        $usersInfo = $this->sharedData['usersInfo'];
 
        $configs = $this->sharedData['configs'];
 
        $userNameEmbed = '<a href="' . $usersInfo['urls'][$action->getUserId()] . '"><b>' . $usersInfo['names'][$action->getUserId()] . '</b></a>';
        $assigns = empty($data['assign']) ? array() : $data['assign'];
        $replaces = array_merge(array(
            'user' => $userNameEmbed
        ), $assigns);
 
        $data['content'] = $this->renderContent($data['content']);
 
        foreach ( $assigns as & $item )
        {
            $item = $this->renderContent($item);
        }
 
        $permalink = empty($data['permalink'])
            ? CNEWS_BOL_Service::getInstance()->getActionPermalink($action->getId(), $this->sharedData['feedType'], $this->sharedData['feedId'])
            : null;
 
        $string = $this->getLocalizedText($data['string']);
        $line = $this->getLocalizedText($data['line']);
        
        $creatorsInfo = $this->getActionUsersInfo($data);
        
        $item = array(
            'id' => $action->getId(),
            'view' => $data['view'],
            'toolbar' => $data['toolbar'],
            'string' => $this->processAssigns($string, $assigns),
            'line' => $this->processAssigns($line, $assigns),
            'content' => $this->processAssigns($data['content'], $assigns),
            'context' => $data['context'],
            'entityType' => $data['action']['entityType'],
            'entityId' => $data['action']['entityId'],
            'createTime' => UTIL_DateTime::formatDate($data['action']['createTime']),
            'updateTime' => $action->getUpdateTime(),
            "user" => reset($creatorsInfo),
            'users' => $creatorsInfo,
            'permalink' => $permalink,
            'cycle' => $cycle
        );
 
        $item['autoId'] = $this->autoId;
 
        $item['features'] = $this->getFeatures($data);
        $item['contextActionMenu'] = $this->getContextMenu($data);
        
        return $item;
    }
 
    public function renderMarkup( $cycle = null )
    {
        $item = $this->getTplData($cycle);
        $this->generateJs($item);
        
        $this->assign('item', $item);
        $this->assign("displayType", $this->displayType);
 
        // Only for the item view page
        if ( $this->displayType == CNEWS_CMP_Feed::DISPLAY_TYPE_PAGE )
        {
            $content = null;
            if ( !empty($item["content"]) && is_array($item["content"]) )
            {
                if ( !empty($item["content"]["text"]) )
                {
                    $content = $item["content"]["text"];
                }
                else if ( !empty($item["content"]["status"]) )
                {
                    $content = empty($item["content"]["status"]);
                }
            }
            else if ( !empty($item["content"]) )
            {
                $content = $item["content"];
            }
            
            $description = empty($item["string"]) ? $content : $item["string"];
            PEEP::getDocument()->setDescription($item['user']['name'] . " " . strip_tags($description));
        }
        
        return $this->render();
    }
}