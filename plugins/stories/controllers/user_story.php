<?php

class STORIES_CTRL_UserStory extends PEEP_ActionController
{

    public function index( $params )
    {
        $plugin = PEEP::getPluginManager()->getPlugin('stories');

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'stories', 'main_menu_item');

        if ( !PEEP::getUser()->isAdmin() && !PEEP::getUser()->isAuthorized('stories', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'view');
            throw new AuthorizationException($status['msg']);

            return;
        }

        /*
          @var $service PostService
         */
        $service = PostService::getInstance();

        /*
          @var $userService BOL_UserService
         */
        $userService = BOL_UserService::getInstance();

        /*
          @var $author BOL_User
         */
        if ( !empty($params['user']) )
        {
            $author = $userService->findByUsername($params['user']);
        }
        else
        {
            $author = $userService->findUserById(PEEP::getUser()->getId());
        }

        if ( empty($author) )
        {
            throw new Redirect404Exception();
            return;
        }

        /* Check privacy permissions */
        $eventParams = array(
            'action' => 'stories_view_story_posts',
            'ownerId' => $author->getId(),
            'viewerId' => PEEP::getUser()->getId()
        );

        PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        /* */
        
        
        $displaySocialSharing = true;
        
        try {
            $eventParams = array(
                'action' => 'stories_view_story_posts',
                'ownerId' => $author->getId(),
                'viewerId' => 0
            );

            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $ex )
        {
            $displaySocialSharing  = false;
        }

        
        if ( $displaySocialSharing && !BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(0, 'stories', 'view')  )
        {
            $displaySocialSharing  = false;
        }
        
        $this->assign('display_social_sharing', $displaySocialSharing);

        $displayName = $userService->getDisplayName($author->getId());

        $this->assign('author', $author);
        $this->assign('username', $author->getUsername());
        $this->assign('displayname', $displayName);

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'user_story_page_heading', array('name' => $author->getUsername())));
        $this->setPageHeadingIconClass('peep_ic_write');

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;

        $rpp = (int) PEEP::getConfig()->getValue('stories', 'results_per_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        if ( !empty($_GET['month']) )
        {
            $archive_params = htmlspecialchars($_GET['month']);
            $arr = explode('-', $archive_params);
            $month = $arr[0];
            $year = $arr[1];

            $lb = mktime(null, null, null, $month, 1, $year);
            $ub = mktime(null, null, null, $month + 1, null, $year);

            $list = $service->findUserPostListByPeriod($author->getId(), $lb, $ub, $first, $count);

            $itemsCount = $service->countUserPostByPeriod($author->getId(), $lb, $ub);

            $l = PEEP::getLanguage();
            $arciveHeaderPart = ', ' . $l->text('base', "month_{$month}") . " {$year} " . $l->text('base', 'archive');

            PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'user_story_archive_title', array('month_name'=>$l->text('base', "month_{$month}"), 'display_name'=>$displayName)));
            PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'user_story_archive_description', array('year'=>$year, 'month_name'=>$l->text('base', "month_{$month}"), 'display_name'=>$displayName) ));
        }
        else
        {
            $list = $service->findUserPostList($author->getId(), $first, $count);

            $itemsCount = $service->countUserPost($author->getId());

            PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'user_story_title', array('display_name'=>$displayName)));
            PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'user_story_description', array('display_name'=>$displayName) ));
        }

        $this->assign('archiveHeaderPart', (!empty($arciveHeaderPart) ? $arciveHeaderPart : ''));

        $posts = array();

        $commentInfo = array();

        $idList = array();

        foreach ( $list as $dto ) /* @var dto Post */
        {
            $idList[] = $dto->getId();
            $dto_post = BASE_CMP_TextFormatter::fromBBtoHtml($dto->getPost());

            $dto->setPost($dto_post);
            $parts = explode('<!--more-->', $dto->getPost());

            if (!empty($parts))
            {
                $text = $parts[0];
                //$text = UTIL_HtmlTag::sanitize($text);
            }
            else
            {
                $text = $dto->getPost();
            }

            $posts[] = array(
                'id' => $dto->getId(),
                'href' => PEEP::getRouter()->urlForRoute('user-post', array('id' => $dto->getId())),
                'title' => UTIL_String::truncate($dto->getTitle(), 65, '...'),
                'text' => UTIL_String::truncate( strip_tags($dto->getpost()), 139, "<!--more-->" ),
                'truncated' => (count($parts) > 1) ? true: true,
            );

        }

        if ( !empty($idList) )
        {
            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('story-post', $idList);
            $this->assign('commentInfo', $commentInfo);

            $tagsInfo = BOL_TagService::getInstance()->findTagListByEntityIdList('story-post', $idList);
            $this->assign('tagsInfo', $tagsInfo);

            $tb = array();

            foreach ( $list as $dto ) /* @var dto Post */
            {

                $tb[$dto->getId()] = array(
                    array(
                        'label' => UTIL_DateTime::formatDate($dto->timestamp)
                    ),
                );

                

                if ( $tagsInfo[$dto->getId()] )
                {
                    $tags = &$tagsInfo[$dto->getId()];
                    $t = PEEP::getLanguage()->text('stories', 'tags');
                    for ( $i = 0; $i < (count($tags) > 3 ? 3 : count($tags)); $i++ )
                    {
                        $t .= " <a href=\"" . PEEP::getRouter()->urlForRoute('stories.list', array('list'=>'browse-by-tag')) . "?tag={$tags[$i]}\">{$tags[$i]}</a>" . ( $i != 2 ? ',' : '' );
                    }

                    $tb[$dto->getId()][] = array('label' => mb_substr($t, 0, mb_strlen($t) - 1));
                }
            }

            $this->assign('tb', $tb);
        }

        $this->assign('list', $posts);

        $info = array(
            'lastPost' => $service->findUserLastPost($author->getId()),
            'author' => $author,
        );

        $this->assign('info', $info);

        $paging = new BASE_CMP_Paging($page, ceil($itemsCount / $rpp), 5);

        $this->assign('paging', $paging->render());

        $rows = $service->findUserArchiveData($author->getId());

        $archive = array();

        foreach ( $rows as $row )
        {
            if ( !array_key_exists($row['y'], $archive) )
            {
                $archive[$row['y']] = array();
            }

            $archive[$row['y']][] = $row['m'];
        }
        $this->assign('archive', $archive);

        $this->assign('my_drafts_url', PEEP::getRouter()->urlForRoute('story-manage-drafts'));

        if (PEEP::getUser()->isAuthenticated())
        {
        $isOwner = ( $params['user'] == PEEP::getUser()->getUserObject()->getUsername() ) ? true : false;
        }
        else
        {
            $isOwner = false;
        }

        $this->assign('isOwner', $isOwner);
    }
}