<?php

class BASE_CTRL_Comments extends PEEP_ActionController
{
    /**
     * @var BOL_CommentService
     */
    private $commentService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->commentService = BOL_CommentService::getInstance();
    }

    public function addComment()
    {
        $errorMessage = false;
        $isMobile = !empty($_POST['isMobile']) && (bool) $_POST['isMobile'];
        $params = $this->getParamsObject();

        if ( empty($_POST['commentText']) && empty($_POST['attachmentInfo']) && empty($_POST['oembedInfo']) )
        {
            $errorMessage = PEEP::getLanguage()->text('base', 'comment_required_validator_message');
        }
        else if ( !PEEP::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(PEEP::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = PEEP::getLanguage()->text('base', 'user_block_message');
        }

        if ( $errorMessage )
        {
            exit(json_encode(array('error' => $errorMessage)));
        }

        $commentText = empty($_POST['commentText']) ? '' : trim($_POST['commentText']);
        $attachment = null;

        if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() && !$isMobile )
        {
            if ( !empty($_POST['attachmentInfo']) )
            {
                $tempArr = json_decode($_POST['attachmentInfo'], true);
                PEEP::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));
                $tempArr['href'] = $tempArr['url'];
                $tempArr['type'] = 'photo';
                $attachment = json_encode($tempArr);
            }
            else if ( !empty($_POST['oembedInfo']) )
            {
                $tempArr = json_decode($_POST['oembedInfo'], true);
                // add some actions
                $attachment = json_encode($tempArr);
            }
        }

        $comment = $this->commentService->addComment($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), PEEP::getUser()->getId(), $commentText, $attachment);

        // trigger event comment add
        $event = new PEEP_Event('base_add_comment', array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'userId' => PEEP::getUser()->getId(),
            'commentId' => $comment->getId(),
            'pluginKey' => $params->getPluginKey(),
            'attachment' => json_decode($attachment, true)
        ));

        PEEP::getEventManager()->trigger($event);

        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_comment');

        if ( $isMobile )
        {
            $commentListCmp = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        }
        else
        {
            $commentListCmp = new BASE_CMP_CommentsList($params, $_POST['cid']);
        }

        exit(json_encode(array(
            'newAttachUid' => $this->commentService->generateAttachmentUid($params->getEntityType(), $params->getEntityId()),
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'commentList' => $commentListCmp->render(),
            'onloadScript' => PEEP::getDocument()->getOnloadScript(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
                )
            )
        );
    }

    public function getCommentList()
    {
        $params = $this->getParamsObject();

        $page = ( isset($_POST['page']) && (int) $_POST['page'] > 0) ? (int) $_POST['page'] : 1;
        $commentsList = new BASE_CMP_CommentsList($params, $_POST['cid'], $page);
        exit(json_encode(array(
            'onloadScript' => PEEP::getDocument()->getOnloadScript(),
            'commentList' => $commentsList->render(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
        )));
    }

    public function getMobileCommentList()
    {
        $params = $this->getParamsObject();
        $commentsList = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        exit(json_encode(array('onloadScript' => PEEP::getDocument()->getOnloadScript(), 'commentList' => $commentsList->render())));
    }

    public function deleteComment()
    {
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $commentEntity = $commentArray['commentEntity'];
        $this->deleteAttachmentFiles($comment);
        $this->commentService->deleteComment($comment->getId());
        $commentCount = $this->commentService->findCommentCount($commentEntity->getEntityType(), $commentEntity->getEntityId());

        if ( $commentCount === 0 )
        {
            $this->commentService->deleteCommentEntity($commentEntity->getId());
        }

        $event = new PEEP_Event('base_delete_comment', array(
            'entityType' => $commentEntity->getEntityType(),
            'entityId' => $commentEntity->getEntityId(),
            'userId' => $comment->getUserId(),
            'commentId' => $comment->getId()
        ));

        PEEP::getEventManager()->trigger($event);

        $this->getCommentList();
    }

    public function deleteCommentAtatchment()
    {
        /* @var $comment BOL_Comment */
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $this->deleteAttachmentFiles($comment);

        if ( !trim($comment->getMessage()) )
        {
            $this->commentService->deleteComment($comment->getId());
        }
        else
        {
            $comment->setAttachment(null);
            $this->commentService->updateComment($comment);
        }

        exit;
    }

    private function deleteAttachmentFiles( BOL_Comment $comment )
    {
        // delete attachments
        $attch = $comment->getAttachment();

        if ( $attch !== null )
        {
            $tempArr = json_decode($attch, true);

            if ( !empty($tempArr['uid']) && !empty($tempArr['pluginKey']) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle($tempArr['pluginKey'], $tempArr['uid']);
            }
        }
    }

    private function getCommentInfoForDelete()
    {
        if ( !isset($_POST['commentId']) || (int) $_POST['commentId'] < 1 )
        {
            echo json_encode(array('error' => PEEP::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        /* @var $comment BOL_Comment */
        $comment = $this->commentService->findComment((int) $_POST['commentId']);
        /* @var $commentEntity BOL_CommentEntity */
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());

        if ( $comment === null || $commentEntity === null )
        {
            echo json_encode(array('error' => PEEP::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        $params = $this->getParamsObject();

        $isModerator = PEEP::getUser()->isAuthorized($params->getPluginKey());
        $isOwnerAuthorized = (PEEP::getUser()->isAuthenticated() && $params->getOwnerId() !== null && (int) $params->getOwnerId() === (int) PEEP::getUser()->getId());
        $commentOwner = ( (int) PEEP::getUser()->getId() === (int) $comment->getUserId() );

        if ( !$isModerator && !$isOwnerAuthorized && !$commentOwner )
        {
            echo json_encode(array('error' => PEEP::getLanguage()->text('base', 'auth_ajax_error')));
            exit();
        }

        return array('comment' => $comment, 'commentEntity' => $commentEntity);
    }

    private function getParamsObject()
    {
        $errorMessage = false;

        $entityType = !isset($_POST['entityType']) ? null : trim($_POST['entityType']);
        $entityId = !isset($_POST['entityId']) ? null : (int) $_POST['entityId'];
        $pluginKey = !isset($_POST['pluginKey']) ? null : trim($_POST['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = PEEP::getLanguage()->text('base', 'comment_ajax_error');
        }

        $params = new BASE_CommentsParams($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($_POST['ownerId']) )
        {
            $params->setOwnerId((int) $_POST['ownerId']);
        }

        if ( isset($_POST['commentCountOnPage']) )
        {
            $params->setCommentCountOnPage((int) $_POST['commentCountOnPage']);
        }

        if ( isset($_POST['displayType']) )
        {
            $params->setDisplayType($_POST['displayType']);
        }

        if ( isset($_POST['initialCount']) )
        {
            $params->setInitialCommentsCount((int) $_POST['initialCount']);
        }

        if ( isset($_POST['loadMoreCount']) )
        {
            $params->setLoadMoreCount((int) $_POST['loadMoreCount']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
                'error' => $errorMessage
            ));

            exit();
        }

        return $params;
    }
}
