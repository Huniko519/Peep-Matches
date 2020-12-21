<?php

class STORIES_CTRL_View extends PEEP_ActionController
{

    public function index( $params )
    {

        $username = !empty($params['user']) ? $params['user'] : '';

        $id = $params['id'];

        $plugin = PEEP::getPluginManager()->getPlugin('stories');

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'stories', 'main_menu_item');

        $service = PostService::getInstance();

        $userService = BOL_UserService::getInstance();

        $this->assign('user', ((PEEP::getUser()->getId() !== null) ? $userService->findUserById(PEEP::getUser()->getId()) : null));

        $post = $service->findById($id);

        if ( $post === null )
        {
            throw new Redirect404Exception();
        }

        if ($post->isDraft() && $post->authorId != PEEP::getUser()->getId())
        {
            throw new Redirect404Exception();
        }

        $post->post = BASE_CMP_TextFormatter::fromBBtoHtml($post->post);
        $post->setTitle( strip_tags($post->getTitle()) );

        if ( !PEEP::getUser()->isAuthorized('stories', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'view');
            throw new AuthorizationException($status['msg']);

            return;
        }

        if ( ( PEEP::getUser()->isAuthenticated() && PEEP::getUser()->getId() != $post->getAuthorId() ) && !PEEP::getUser()->isAuthorized('stories', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'view');
            throw new AuthorizationException($status['msg']);

            return;
        }

        /* Check privacy permissions */
        if ( $post->authorId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('stories') )
        {
            $eventParams = array(
                'action' => 'stories_view_story_posts',
                'ownerId' => $post->authorId,
                'viewerId' => PEEP::getUser()->getId()
            );

            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        /* */

        $parts = explode('<!--page-->', $post->getPost());

        $page = !empty($_GET['page']) ? $_GET['page'] : 1;

        $count = count($parts);

        if ( strlen($username) > 0 )
        {
            $author = $userService->findByUsername($username);
        }
        else
        {
            $author = $userService->findUserById($post->getAuthorId());
            $isAuthorExists = !empty($author);
            if ( $isAuthorExists )
            {
                $username = $author->getUsername();
            }
        }

        $this->assign('isAuthorExists', $isAuthorExists);

        if ( $isAuthorExists )
        {
            $displayName = $userService->getDisplayName($author->getId());

            $this->assign('username', $userService->getUserName($author->getId()));
            $this->assign('displayname', $displayName);

            $url = PEEP::getRouter()->urlForRoute('user-story', array('user' => $username));

            $pending_approval_text = '';
            if ($post->getStatus() == PostService::POST_STATUS_APPROVAL)
            {
                $pending_approval_text = '<span class="peep_remark peep_small">('.PEEP::getLanguage()->text('base', 'pending_approval').')</span>';
            }
            $this->setPageHeading(PEEP::getLanguage()->text('stories', 'view_page_heading', array('url' => $url, 'name' => $displayName, 'postTitle' => htmlspecialchars($post->getTitle()))) .' '. $pending_approval_text );
            $this->setPageHeadingIconClass('peep_ic_write');

            PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'story_post_title', array('post_title' => htmlspecialchars($post->getTitle()), 'display_name' => $displayName)));

            $post_body = UTIL_String::truncate($post->getPost(), 200, '...');
            $postTagsArray = BOL_TagService::getInstance()->findEntityTags($post->getId(), 'story-post');
            $postTags = "";

            foreach ( $postTagsArray as $tag )
            {
                $postTags .= $tag->label . ", ";
            }
            $postTags = substr($postTags, 0, -2);
            PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'story_post_description', array('post_body' => htmlspecialchars(strip_tags($post_body)), 'tags' => htmlspecialchars($postTags))));
            //PEEP::getDocument()->setKeywords(PEEP::getLanguage()->text('nav', 'page_default_keywords').", ".$postTags);
        }



        $info = array(
            'dto' => $post,
            'text' => $parts[$page - 1]
        );

        $this->assign('info', $info);

        if ( $isAuthorExists )
        {
            //story navigation
            $prev = $service->findAdjacentUserPost($author->getId(), $post->getId(), 'prev');
            $next = $service->findAdjacentUserPost($author->getId(), $post->getId(), 'next');

            if ( !empty($prev) )
            {
                $prevUser = $userService->findUserById($prev->getAuthorId());
            }

            if ( !empty($next) )
            {
                $nextUser = $userService->findUserById($next->getAuthorId());
            }

            $this->assign('adjasentUrl',
                array(
                    'next' => (!empty($nextUser) ) ? PEEP::getRouter()->urlForRoute('user-post', array('id' => $next->getId(), 'user' => $nextUser->getUsername())) : '',
                    'prev' => (!empty($prevUser) ) ? PEEP::getRouter()->urlForRoute('user-post', array('id' => $prev->getId(), 'user' => $prevUser->getUsername())) : '',
                    'index' => PEEP::getRouter()->urlForRoute('user-story', array('user' => $author->getUsername()))
                )
            );
        }
        else
        {
            $this->assign('adjasentUrl', null);
        }
        //~story navigation
        //toolbar

        $tb = array();

        $toolbarEvent = new BASE_CLASS_EventCollector('stories.collect_post_toolbar_items', array(
            'postId' => $post->id,
            'postDto' => $post
        ));

        PEEP::getEventManager()->trigger($toolbarEvent);

        foreach ( $toolbarEvent->getData() as $toolbarItem )
        {
            array_push($tb, $toolbarItem);
        }

        if ($post->getStatus() == PostService::POST_STATUS_APPROVAL && PEEP::getUser()->isAuthorized('stories'))
        {
            $tb[] = array(
                'label' => PEEP::getLanguage()->text('base', 'approve'),
                'href' => PEEP::getRouter()->urlForRoute('post-approve', array('id'=>$post->getId())),
                'id' => 'story_post_toolbar_approve',
                'class'=>'peep_mild_green'
            );
        }

        if ( PEEP::getUser()->isAuthenticated() && ( $post->getAuthorId() != PEEP::getUser()->getId() ) )
        {
            $js = UTIL_JsGenerator::newInstance()
                ->jQueryEvent('#story_post_toolbar_flag', 'click', UTIL_JsGenerator::composeJsString('PEEP.flagContent({$entityType}, {$entityId});',
                            array(
                        'entityType' => PostService::FEED_ENTITY_TYPE,
                        'entityId' => $post->getId()
            )));

            PEEP::getDocument()->addOnloadScript($js, 1001);

            $tb[] = array(
                'label' => PEEP::getLanguage()->text('base', 'flag'),
                'href' => 'javascript://',
                'id' => 'story_post_toolbar_flag'
            );
        }
        if ( PEEP::getUser()->isAuthenticated() && ( PEEP::getUser()->getId() == $post->getAuthorId() || PEEP::getUser()->isAuthorized('stories') ) )
        {
            $tb[] = array(
                'href' => PEEP::getRouter()->urlForRoute('post-save-edit', array('id' => $post->getId())),
                'label' => PEEP::getLanguage()->text('stories', 'toolbar_edit')
            );

            $tb[] = array(
                'href' => PEEP::getRouter()->urlFor('STORIES_CTRL_Save', 'delete', array('id' => $post->getId())),
                'click' => "return confirm('" . PEEP::getLanguage()->text('base', 'are_you_sure') . "');",
                'label' => PEEP::getLanguage()->text('stories', 'toolbar_delete')
            );
        }

        $this->assign('tb', $tb);
        //~toolbar

        $paging = new BASE_CMP_Paging($page, $count, $count);

        //<ARCHIVE-NAVIGATOR>


        $this->assign('paging', $paging->render());
        if ( $isAuthorExists )
        {
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
        }

        //</ARCHIVE-NAVIGATOR>
        if ( $isAuthorExists )
        {
            $this->assign('author', $author);
        }

        $this->assign('isModerator', PEEP::getUser()->isAuthorized('stories'));
        if ( $isAuthorExists )
        {
            $this->assign('userStoryUrl', PEEP::getRouter()->urlForRoute('user-story', array('user' => $author->getUsername())));
        }

        $rateInfo = new BASE_CMP_Rate('stories', 'story-post', $post->getId(), $post->getAuthorId());

        /* Check comments privacy permissions */
        $allow_comments = true;
        if ($post->getStatus() == PostService::POST_STATUS_APPROVAL)
        {
            $allow_comments = false;
            $rateInfo->setVisible(false);
        }
        else
        {
            if ( $post->authorId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('stories') )
            {
                $eventParams = array(
                    'action' => 'stories_comment_story_posts',
                    'ownerId' => $post->authorId,
                    'viewerId' => PEEP::getUser()->getId()
                );

                try
                {
                    PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $ex )
                {
                    $allow_comments = false;
                }
            }
        }
        /* */

        $this->addComponent('rate', $rateInfo);

        // additional components
        $cmpParams = new BASE_CommentsParams('stories', 'story-post');
        $cmpParams->setEntityId($post->getId())
            ->setOwnerId($post->getAuthorId())
            ->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST)
            ->setAddComment($allow_comments);

        $this->addComponent('comments', new BASE_CMP_Comments($cmpParams));

        $this->assign('avatarUrl', '');

        $tagCloud = new BASE_CMP_EntityTagCloud('story-post', PEEP::getRouter()->urlForRoute('stories.list', array('list'=>'browse-by-tag')));

        $tagCloud->setEntityId($post->getId());

        $this->addComponent('tagCloud', $tagCloud);
        //~ additional components
    }

    public function approve($params)
    {
        if (!PEEP::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }

        if (!PEEP::getUser()->isAuthorized('stories'))
        {
            throw new Redirect403Exception();
        }

        //TODO trigger event for content moderation;
        $postId = $params['id'];
        $postDto = PostService::getInstance()->findById($postId);
        if (!$postDto)
        {
            throw new Redirect404Exception();
        }

        $backUrl = PEEP::getRouter()->urlForRoute('post', array('id'=>$postId));

        $event = new PEEP_Event("moderation.approve", array(
            "entityType" => PostService::FEED_ENTITY_TYPE,
            "entityId" => $postId
        ));

        PEEP::getEventManager()->trigger($event);

        $data = $event->getData();
        if ( empty($data) )
        {
            $this->redirect($backUrl);
        }

        if ( $data["message"] )
        {
            PEEP::getFeedback()->info($data["message"]);
        }
        else
        {
            PEEP::getFeedback()->error($data["error"]);
        }

        $this->redirect($backUrl);
    }
}