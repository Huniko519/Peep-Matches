<?php

class CNEWS_CTRL_Ajax extends PEEP_ActionController
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

    public function init()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
    }

    public function like()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $entityType = !empty($_POST['entityType']) ?  $_POST['entityType'] : null;
        $entityId = !empty($_POST['entityId']) ? $_POST['entityId'] : null;

        $like = $this->service->addLike(PEEP::getUser()->getId(), $entityType, $entityId);

        $event = new PEEP_Event('feed.after_like_added', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => PEEP::getUser()->getId()
        ), array(
            'likeId' => $like->id
        ));

        PEEP::getEventManager()->trigger($event);
        
        $this->afterLike($entityType, $entityId);
    }
    
    protected function afterLike( $entityType, $entityId )
    {
        $cmp = new CNEWS_CMP_Likes($entityType, $entityId);

        echo json_encode(array(
            'count' => $cmp->getCount(),
            'markup' => $cmp->render()
        ));

        exit;
    }

    public function unlike()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $entityType = !empty($_POST['entityType']) ?  $_POST['entityType'] : null;
        $entityId = !empty($_POST['entityId']) ? (int) $_POST['entityId'] : null;

        $this->service->removeLike(PEEP::getUser()->getId(), $entityType, $entityId);

        $event = new PEEP_Event('feed.after_like_removed', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => PEEP::getUser()->getId()
        ));

        PEEP::getEventManager()->trigger($event);
        
        $this->afterUnlike($entityType, $entityId);
    }
    
    protected function afterUnlike( $entityType, $entityId )
    {
        $this->afterLike($entityType, $entityId);
    }

    public function statusUpdate()
    {
        if ( empty($_POST['status']) && empty($_POST['attachment']) )
        {
            echo json_encode(array(
                "error" => PEEP::getLanguage()->text('base', 'form_validate_common_error_message')
            ));
            exit;
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            echo json_encode(false);
            exit;
        }

        $oembed = null;
        $attachId = null;
        $status = empty($_POST['status']) ? '' : strip_tags($_POST['status']);
        $content = array();

        if ( !empty($_POST['attachment']) )
        {
            $content = json_decode($_POST['attachment'], true);

            if ( !empty($content) )
            {
                if( $content['type'] == 'photo' && !empty($content['uid']) )
                {
                    $attachmentData = PEEP::getEventManager()->call('base.attachment_save_image', array( 
                        "pluginKey" => "cnews",
                        'uid' => $content['uid']
                    ));
                    
                    $content['url'] = $content['href'] = $attachmentData["url"];
                    $attachId = $content['uid'];
                }

                if( $content['type'] == 'video' )
                {
                    $content['html'] = BOL_TextFormatService::getInstance()->validateVideoCode($content['html']);
                }
            }
        }
        
        $userId = PEEP::getUser()->getId();
        
        $event = new PEEP_Event("feed.before_content_add", array(
            "feedType" => $_POST['feedType'],
            "feedId" => $_POST['feedId'],
            "visibility" => $_POST['visibility'],
            "userId" => $userId,
            "status" => $status,
            "type" => empty($content["type"]) ? "text" : $content["type"],
            "data" => $content
        ));
        
        PEEP::getEventManager()->trigger($event);
        
        $data = $event->getData();
        
        if ( !empty($data) )
        {
            if ( !empty($attachId) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("cnews", $attachId);
            }
            
            $item = empty($data["entityType"]) || empty($data["entityId"])
                    ? null
                    : array(
                        "entityType" => $data["entityType"],
                        "entityId" => $data["entityId"]
                    );
            
            echo json_encode(array(
                "item" => $item,
                "message" => empty($data["message"]) ? null : $data["message"],
                "error" => empty($data["error"]) ? null : $data["error"]
            ));
            exit;
        }
        
        $status = UTIL_HtmlTag::autoLink($status);
        $out = CNEWS_BOL_Service::getInstance()
                ->addStatus(PEEP::getUser()->getId(), $_POST['feedType'], $_POST['feedId'], $_POST['visibility'], $status, array(
                    "content" => $content,
                    "attachmentId" => $attachId
                ));
        
        echo json_encode(array(
            "item" => $out
        ));
        exit;
    }

    public function remove()
    {
        $id = !empty($_POST['actionId']) ? (int) $_POST['actionId'] : null;

        if ( !$id )
        {
            throw new Redirect404Exception();
        }

        $dto = $this->service->findActionById($id);

        if ( empty($dto) )
        {
            exit;
        }

        // check permissions
        $removeAllowed = PEEP::getUser()->isAuthorized("cnews");

        if ( !$removeAllowed )
        {
            $activities = $this->service->
                    findActivity(CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $dto->id);

            // check for the ownership
            foreach ($activities as $activity) {
                if ( PEEP::getUser()->getId() == $activity->userId ) {
                    $removeAllowed = true;
                    break;
                }
            }
        }

        if ( $removeAllowed )
        {
            $this->service->removeActionById($id);
            echo json_encode(PEEP::getLanguage()->text('cnews', 'item_deleted_feedback'));            
        }

        exit;
    }

    public function removeAttachment()
    {
        $id = !empty($_POST['actionId']) ? (int) $_POST['actionId'] : null;

        if ( !$id )
        {
            throw new Redirect404Exception();
        }

        $dto = $this->service->findActionById($id);
        $data = json_decode($dto->data, true);

        if( !empty($data['attachmentId']) )
        {
            BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("cnews", $data['attachmentId']);
        }

        unset($data['attachment']);
        $dto->data = json_encode($data);

        $this->service->saveAction($dto);

        exit;
    }

    public function loadItem()
    {
        $params = json_decode($_GET['p'], true);

        $feedData = $params['feedData'];

        $driverClass = $feedData['driver']['class'];
        /* @var $driver CNEWS_CLASS_Driver */
        $driver = PEEP::getClassInstance($driverClass);
        $driver->setup($feedData['driver']['params']);

        if ( isset($params['actionId']) )
        {
            $action = $driver->getActionById($params['actionId']);
        }
        else if ( isset($params['entityType']) && isset($params['entityId']) )
        {
            $action = $driver->getAction($params['entityType'], $params['entityId']);
        }
        else
        {
            throw new InvalidArgumentException('Invalid paraeters: `entityType` and `entityId` or `actionId`');
        }

        if ( $action === null )
        {
            $this->echoError('Action not found');
        }

        $data = $feedData['data'];

        $sharedData['feedAutoId'] = $data['feedAutoId'];
        $sharedData['feedType'] = $data['feedType'];
        $sharedData['feedId'] = $data['feedId'];

        $sharedData['configs'] = PEEP::getConfig()->getValues('cnews');

        $userIdList = array($action->getUserId());
        $sharedData['usersIdList'] = $userIdList;

        $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);

        $sharedData['usersInfo']['avatars'][$action->getUserId()] = $usersInfo[$action->getUserId()]['src'];
        $sharedData['usersInfo']['urls'][$action->getUserId()] = $usersInfo[$action->getUserId()]['url'];
        $sharedData['usersInfo']['names'][$action->getUserId()] = $usersInfo[$action->getUserId()]['title'];
        $sharedData['usersInfo']['roleLabels'][$action->getUserId()] = array(
            'label' => $usersInfo[$action->getUserId()]['label'],
            'labelColor' => $usersInfo[$action->getUserId()]['labelColor']
        );

        $entityList = array();
        $entityList[] = array(
            'entityType' => $action->getEntity()->type,
            'entityId' => $action->getEntity()->id,
            'pluginKey' => $action->getPluginKey(),
            'userId' => $action->getUserId(),
            'countOnPage' => $sharedData['configs']['comments_count']
        );

        $sharedData['commentsData'] = BOL_CommentService::getInstance()->findBatchCommentsData($entityList);
        $sharedData['likesData'] = CNEWS_BOL_Service::getInstance()->findLikesByEntityList($entityList);

        $cmp = $this->createFeedItem($action, $sharedData);
        $cmp->setDisplayType($data['displayType']);
        $html = $cmp->renderMarkup(empty($params['cycle']) ? null : $params['cycle']);

        $this->synchronizeData($data['feedAutoId'], array(
            'data' => $data,
            'driver' => $driver->getState()
        ));

        $this->echoMarkup($html);
    }
    
    /**
     * 
     * @param CNEWS_CLASS_Action $action
     * @param array $sharedData
     * @return CNEWS_CMP_FeedItem
     */
    protected function createFeedItem( $action, $sharedData )
    {
        return PEEP::getClassInstance("CNEWS_CMP_FeedItem", $action, $sharedData);
    }

    public function loadItemList()
    {
        $params = json_decode($_GET['p'], true);

        $event = new PEEP_Event('feed.on_ajax_load_list', $params);
        PEEP::getEventManager()->trigger($event);

        $driverClass = $params['driver']['class'];

        /*@var $cmp CNEWS_CLASS_Driver */
        $driver = PEEP::getClassInstance($driverClass);

        $driverParams = $params['driver']['params'];
        $driverParams['displayCount'] = $driverParams['displayCount'] > 20 ? 20 : $driverParams['displayCount'];

        $driver->setup($driverParams);

        $driver->moveCursor();
        $actionList = $driver->getActionList();

        $list = $this->createFeedList($actionList, $params['data']);
        $list->setDisplayType($params['data']['displayType']);
        $html = $list->render();

        $this->synchronizeData($params['data']['feedAutoId'], array(
            'data' => $params['data'],
            'driver' => $driver->getState()
        ));

        $this->echoMarkup($html);
    }

    /**
     * 
     * @param array $actionList
     * @param array $data
     * @return CNEWS_CMP_FeedList
     */
    protected function createFeedList( $actionList, $data )
    {
        return PEEP::getClassInstance("CNEWS_CMP_FeedList", $actionList, $data);
    }
    
    private function synchronizeData( $autoId, $data )
    {
        $script = UTIL_JsGenerator::newInstance()
                ->callFunction(array('window', 'peep_cnews_feed_list', $autoId, 'setData'), array($data));
        PEEP::getDocument()->addOnloadScript($script);
    }

    private function echoError( $msg, $code = null )
    {
        echo json_encode(array(
            'result' => 'error',
            'code' => $code,
            'msg' => $msg
        ));

        exit;
    }

    private function echoMarkup( $html )
    {
        /* @var $document PEEP_AjaxDocument */
        $document = PEEP::getDocument();

        $markup = array();

        $markup['result'] = 'success';
        $markup['html'] = trim($html);

        $beforeIncludes = $document->getScriptBeforeIncludes();
        if ( !empty($beforeIncludes) )
        {
            $markup['beforeIncludes'] = $beforeIncludes;
        }

        $scripts = $document->getScripts();
        if ( !empty($scripts) )
        {
            $markup['scriptFiles'] = $scripts;
        }

        $styleSheets = $document->getStyleSheets();
        if ( !empty($styleSheets) )
        {
            $markup['styleSheets'] = $styleSheets;
        }

        $onloadScript = $document->getOnloadScript();
        if ( !empty($onloadScript) )
        {
            $markup['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if ( !empty($styleDeclarations) )
        {
            $markup['styleDeclarations'] = $styleDeclarations;
        }

        echo json_encode($markup);

        exit;
    }
}
