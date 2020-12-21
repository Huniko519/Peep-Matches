<?php

class STORIES_CTRL_ManagementComment extends PEEP_ActionController
{

    public function index()
    {

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'management_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_write');

        $this->addComponent('menu', new STORIES_CMP_ManagementMenu());

        $service = PostService::getInstance();

        $userId = PEEP::getUser()->getId();

        $page = empty($_GET['page']) ? 1 : $_GET['page'];

        $this->assign('thisUrl', PEEP_URL_HOME . PEEP::getRequest()->getRequestUri());

        $rpp = 5;

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $list = $service->findUserPostCommentList($userId, $first, $count);
        $authorIdList = array();
        $postList = array();
        foreach ( $list as $id => $item )
        {
            if ( !empty($info[$item['userId']]) )
            {
                continue;
            }

            $list[$id]['url'] = PEEP::getRouter()->urlForRoute('user-post', array('id'=>$item['entityId']));
            $postList[$item['entityId']] = $service->findById($item['entityId']);
            $authorIdList[] = $item['userId'];
        }

        $usernameList = array();
        $displayNameList = array();
        $avatarUrlList = array();

        if ( !empty($authorIdList) )
        {
            $userService = BOL_UserService::getInstance();

            $usernameList = $userService->getUserNamesForList($authorIdList);
            $displayNameList = $userService->getDisplayNamesForList($authorIdList);
            $avatarUrlList = BOL_AvatarService::getInstance()->getAvatarsUrlList($authorIdList);
        }

        $this->assign('postList', $postList);
        $this->assign('usernameList', $usernameList);
        $this->assign('displaynameList', $displayNameList);
        $this->assign('avatarUrlList', $avatarUrlList);

        $this->assign('list', $list);

        $itemCount = $service->countUserPostComment($userId);

        $pageCount = ceil($itemCount / $rpp);

        $this->addComponent('paging', new BASE_CMP_Paging($page, $pageCount, 5));
    }

    public function deleteComment( $params )
    {

        if ( empty($params['id']) || intval($params['id']) <= 0 )
        {
            throw new InvalidArgumentException();
        }

        $id = (int) $params['id'];

        $isAuthorized = true; // TODO: Authorization needed

        if ( !$isAuthorized )
        {
            exit;
        }

        BOL_CommentService::getInstance()->deleteComment($id);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'manage_page_comment_deleted_msg'));
        $this->redirect($_GET['back-to']);
    }
}