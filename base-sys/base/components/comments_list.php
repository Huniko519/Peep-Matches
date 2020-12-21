<?php

class BASE_CMP_CommentsList extends PEEP_Component
{
    /**
     * @var BASE_CommentsParams
     */
    protected $params;
    protected $batchData;
    protected $staticData;
    protected $id;
    protected $commentCount;
    protected $cmpContextId;

    /**
     * @var BOL_CommentService
     */
    protected $commentService;
    protected $avatarService;
    protected $page;
    protected $isModerator;
    protected $actionArr = array('comments' => array(), 'users' => array());
    protected $commentIdList = array();
    protected $userIdList = array();

    /**
     * Constructor.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $page
     * @param string $displayType
     */
    public function __construct( BASE_CommentsParams $params, $id, $page = 1 )
    {
        parent::__construct();
        $batchData = $params->getBatchData();
        $this->staticData = empty($batchData['_static']) ? array() : $batchData['_static'];
        $batchData = isset($batchData[$params->getEntityType()][$params->getEntityId()]) ? $batchData[$params->getEntityType()][$params->getEntityId()] : array();
        $this->params = $params;
        $this->batchData = $batchData;
        $this->id = $id;
        $this->page = $page;
        $this->isModerator = PEEP::getUser()->isAuthorized($params->getPluginKey());
        $this->isOwnerAuthorized = (PEEP::getUser()->isAuthenticated() && $this->params->getOwnerId() !== null && (int) $this->params->getOwnerId() === (int) PEEP::getUser()->getId());
        $this->isBaseModerator = PEEP::getUser()->isAuthorized('base');

        $this->commentService = BOL_CommentService::getInstance();
        $this->avatarService = BOL_AvatarService::getInstance();
        $this->cmpContextId = "comments-list-$id";
        $this->assign('cmpContext', $this->cmpContextId);

        $this->commentCount = isset($batchData['commentsCount']) ? $batchData['commentsCount'] : $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId());
        $this->init();
    }

    protected function processList( $commentList )
    {
        $arrayToAssign = array();

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $this->userIdList[] = $value->getUserId();
            $this->commentIdList[] = $value->getId();
        }

        $userAvatarArrayList = empty($this->staticData['avatars']) ? $this->avatarService->getDataForUserAvatars($this->userIdList) : $this->staticData['avatars'];

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $cmItemArray = array(
                'displayName' => $userAvatarArrayList[$value->getUserId()]['title'],
                'avatarUrl' => $userAvatarArrayList[$value->getUserId()]['src'],
                'profileUrl' => $userAvatarArrayList[$value->getUserId()]['url'],
                'content' => $value->getMessage(),
                'date' => UTIL_DateTime::formatDate($value->getCreateStamp()),
                'userId' => $value->getUserId(),
                'commentId' => $value->getId(),
                'avatar' => $userAvatarArrayList[$value->getUserId()],
            );

            $contentAdd = '';

            if ( $value->getAttachment() !== null )
            {
                $tempCmp = new BASE_CMP_OembedAttachment((array) json_decode($value->getAttachment()), $this->isOwnerAuthorized);
                $contentAdd .= '<div class="peep_attachment peep_small" id="att' . $value->getId() . '">' . $tempCmp->render() . '</div>';
            }

            $cmItemArray['content_add'] = $contentAdd;

            $event = new BASE_CLASS_EventProcessCommentItem('base.comment_item_process', $value, $cmItemArray);
            PEEP::getEventManager()->trigger($event);
            $arrayToAssign[] = $event->getDataArr();
        }

        return $arrayToAssign;
    }

    public function itemHandler( BASE_CLASS_EventProcessCommentItem $e )
    {
        $language = PEEP::getLanguage();

        $deleteButton = false;
        $cAction = null;
        $value = $e->getItem();

        if ( $this->isOwnerAuthorized || $this->isModerator || (int) PEEP::getUser()->getId() === (int) $value->getUserId() )
        {
            $deleteButton = true;
        }
        
        $flagButton = $value->getUserId() != PEEP::getUser()->getId();

        if ( $this->isBaseModerator || $deleteButton || $flagButton )
        {
            $cAction = new BASE_CMP_ContextAction();
            $parentAction = new BASE_ContextAction();
            $parentAction->setKey('parent');
            $parentAction->setClass('peep_comments_context');
            $cAction->addAction($parentAction);

            if ( $deleteButton )
            {
                $flagAction = new BASE_ContextAction();
                $flagAction->setLabel($language->text('base', 'contex_action_comment_delete_label'));
                $flagAction->setKey('udel');
                $flagAction->setParentKey($parentAction->getKey());
                $delId = 'del-' . $value->getId();
                $flagAction->setId($delId);
                $this->actionArr['comments'][$delId] = $value->getId();
                $cAction->addAction($flagAction);
            }

            if ( $this->isBaseModerator && $value->getUserId() != PEEP::getUser()->getId() )
            {
                $modAction = new BASE_ContextAction();
                $modAction->setLabel($language->text('base', 'contex_action_user_delete_label'));
                $modAction->setKey('cdel');
                $modAction->setParentKey($parentAction->getKey());
                $delId = 'udel-' . $value->getId();
                $modAction->setId($delId);
                $this->actionArr['users'][$delId] = $value->getUserId();
                $cAction->addAction($modAction);
            }
            
            if ( $flagButton )
            {
                $flagAction = new BASE_ContextAction();
                $flagAction->setLabel($language->text('base', 'flag'));
                $flagAction->setKey('cflag');
                $flagAction->setParentKey($parentAction->getKey());
                $flagAction->addAttribute("onclick", "var d = $(this).data(); PEEP.flagContent(d.etype, d.eid);");
                $flagAction->addAttribute("data-etype", "comment");
                $flagAction->addAttribute("data-eid", $value->id);

                $cAction->addAction($flagAction);
            }
        }

        if ( $this->params->getCommentPreviewMaxCharCount() > 0 && mb_strlen($value->getMessage()) > $this->params->getCommentPreviewMaxCharCount() )
        {
            $e->setDataProp('previewMaxChar', $this->params->getCommentPreviewMaxCharCount());
        }

        $e->setDataProp('cnxAction', empty($cAction) ? '' : $cAction->render());
    }

    protected function init()
    {
        if ( $this->commentCount === 0 && $this->params->getShowEmptyList() )
        {
            $this->assign('noComments', true);
        }

        $countToLoad = 0;

        if ( $this->commentCount === 0 )
        {
            $commentList = array();
        }
        else if ( in_array($this->params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {
            $commentList = empty($this->batchData['commentsList']) ? $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), 1, $this->params->getInitialCommentsCount()) : $this->batchData['commentsList'];
            $commentList = array_reverse($commentList);
            $countToLoad = $this->commentCount - $this->params->getInitialCommentsCount();
            $this->assign('countToLoad', $countToLoad);
        }
        else
        {
            $commentList = $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), $this->page, $this->params->getCommentCountOnPage());
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('base.comment_list_prepare_data', array('list' => $commentList, 'entityType' => $this->params->getEntityType(), 'entityId' => $this->params->getEntityId())));
        PEEP::getEventManager()->bind('base.comment_item_process', array($this, 'itemHandler'));
        $this->assign('comments', $this->processList($commentList));
        $pages = false;

        if ( $this->params->getDisplayType() === BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING )
        {
            $pagesCount = $this->commentService->findCommentPageCount($this->params->getEntityType(), $this->params->getEntityId(), $this->params->getCommentCountOnPage());

            if ( $pagesCount > 1 )
            {
                $pages = $this->getPages($this->page, $pagesCount, 8);
                $this->assign('pages', $pages);
            }
        }
        else
        {
            $pagesCount = 0;
        }

        $this->assign('loadMoreLabel', PEEP::getLanguage()->text('base', 'comment_load_more_label'));

        static $dataInit = false;

        if ( !$dataInit )
        {
            $staticDataArray = array(
                'respondUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Comments', 'getCommentList'),
                'delUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteComment'),
                'delAtchUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteCommentAtatchment'),
                'delConfirmMsg' => PEEP::getLanguage()->text('base', 'comment_delete_confirm_message'),
                'preloaderImgUrl' => PEEP::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'ajax_preloader_button.gif'
            );
            PEEP::getDocument()->addOnloadScript("window.peepCommentListCmps.staticData=" . json_encode($staticDataArray) . ";");
            $dataInit = true;
        }

        $jsParams = json_encode(
            array(
                'totalCount' => $this->commentCount,
                'contextId' => $this->cmpContextId,
                'displayType' => $this->params->getDisplayType(),
                'entityType' => $this->params->getEntityType(),
                'entityId' => $this->params->getEntityId(),
                'pagesCount' => $pagesCount,
                'initialCount' => $this->params->getInitialCommentsCount(),
                'loadMoreCount' => $this->params->getLoadMoreCount(),
                'commentIds' => $this->commentIdList,
                'pages' => $pages,
                'pluginKey' => $this->params->getPluginKey(),
                'ownerId' => $this->params->getOwnerId(),
                'commentCountOnPage' => $this->params->getCommentCountOnPage(),
                'cid' => $this->id,
                'actionArray' => $this->actionArr,
                'countToLoad' => $countToLoad
            )
        );

        PEEP::getDocument()->addOnloadScript(
            "window.peepCommentListCmps.items['$this->id'] = new PeepCommentsList($jsParams);
            window.peepCommentListCmps.items['$this->id'].init();"
        );
    }

    protected function getPages( $currentPage, $pagesCount, $displayPagesCount )
    {
        $first = false;
        $last = false;

        $prev = ( $currentPage > 1 );
        $next = ( $currentPage < $pagesCount );

        if ( $pagesCount <= $displayPagesCount )
        {
            $start = 1;
            $displayPagesCount = $pagesCount;
        }
        else
        {
            $start = $currentPage - (int) floor($displayPagesCount / 2);

            if ( $start <= 1 )
            {
                $start = 1;
            }
            else
            {
                $first = true;
            }

            if ( ($start + $displayPagesCount - 1) < $pagesCount )
            {
                $last = true;
            }
            else
            {
                $start = $pagesCount - $displayPagesCount + 1;
            }
        }

        $pageArray = array();

        if ( $first )
        {
            $pageArray[] = array('label' => PEEP::getLanguage()->text('base', 'paging_label_first'), 'pageIndex' => 1);
        }

        if ( $prev )
        {
            $pageArray[] = array('label' => PEEP::getLanguage()->text('base', 'paging_label_prev'), 'pageIndex' => ($currentPage - 1));
        }

        if ( $first )
        {
            $pageArray[] = array('label' => '...');
        }

        for ( $i = (int) $start; $i <= ($start + $displayPagesCount - 1); $i++ )
        {
            $pageArray[] = array('label' => $i, 'pageIndex' => $i, 'active' => ( $i === (int) $currentPage ));
        }

        if ( $last )
        {
            $pageArray[] = array('label' => '...');
        }

        if ( $next )
        {
            $pageArray[] = array('label' => PEEP::getLanguage()->text('base', 'paging_label_next'), 'pageIndex' => ( $currentPage + 1 ));
        }

        if ( $last )
        {
            $pageArray[] = array('label' => PEEP::getLanguage()->text('base', 'paging_label_last'), 'pageIndex' => $pagesCount);
        }

        return $pageArray;
    }
}
