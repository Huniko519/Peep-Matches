<?php

class STORIES_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var STORIES_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return STORIES_CLASS_EventHandler
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
     * @var PostService
     */
    private $service;

    private function __construct()
    {
        $this->service = PostService::getInstance();
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_SUSPEND, array(PostService::getInstance(), 'onAuthorSuspend'));

        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'onUnregisterUser'));
        PEEP::getEventManager()->bind('notifications.collect_actions', array($this, 'onCollectNotificationActions'));
        PEEP::getEventManager()->bind('base_add_comment', array($this, 'onAddStoryPostComment'));
        //PEEP::getEventManager()->bind('base_delete_comment',                array($this, 'onDeleteComment'));
        PEEP::getEventManager()->bind('ads.enabled_plugins', array($this, 'onCollectEnabledAdsPages'));

        PEEP::getEventManager()->bind('admin.add_auth_labels', array($this, 'onCollectAuthLabels'));
        PEEP::getEventManager()->bind('feed.collect_configurable_activity', array($this, 'onCollectFeedConfigurableActivity'));
        PEEP::getEventManager()->bind('feed.collect_privacy', array($this, 'onCollectFeedPrivacyActions'));
        PEEP::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActionList'));
        PEEP::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangeActionPrivacy'));

        PEEP::getEventManager()->bind('feed.on_entity_add', array($this, 'onAddStoryPost'));
        PEEP::getEventManager()->bind('feed.on_entity_update', array($this, 'onUpdateStoryPost'));
        PEEP::getEventManager()->bind('feed.after_comment_add', array($this, 'onFeedAddComment'));
        PEEP::getEventManager()->bind('feed.after_like_added', array($this, 'onFeedAddLike'));

        PEEP::getEventManager()->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetStoryInfo'));

        $credits = new STORIES_CLASS_Credits();
        PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        PEEP::getEventManager()->bind('usercredits.get_action_key', array($credits, 'getActionKey'));
    }

    public function onCollectAddNewContentItem( BASE_CLASS_EventCollector $event )
    {
        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'peep_ic_write',
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => PEEP::getLanguage()->text('stories', 'add_new_link'),
            BASE_CMP_AddNewContent::DATA_KEY_ID => 'addNewStoryPostBtn'
        );

        if ( PEEP::getUser()->isAuthenticated() && PEEP::getUser()->isAuthorized('stories', 'add') )
        {
            $resultArray[BASE_CMP_AddNewContent::DATA_KEY_URL] = PEEP::getRouter()->urlForRoute('post-save-new');

            $event->add($resultArray);
        }
        else
        {
            $resultArray[BASE_CMP_AddNewContent::DATA_KEY_URL] = 'javascript://';

            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'add');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $script = '$("#addNewStoryPostBtn").click(function(){
                    PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                });';
                PEEP::getDocument()->addOnloadScript($script);

                $event->add($resultArray);
            }
        }
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'stories',
            'action' => 'stories-add_comment',
            'description' => PEEP::getLanguage()->text('stories', 'email_notifications_setting_comment'),
            'selected' => true,
            'sectionLabel' => PEEP::getLanguage()->text('stories', 'notification_section_label'),
            'sectionIcon' => 'peep_ic_write'
        ));
    }

    public function onAddStoryPostComment( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'story-post' )
            return;

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $postService = PostService::getInstance();

        $post = $postService->findById($entityId);


        if ( $userId == $post->authorId )
        {
            return;
        }

        $actor = array(
            'name' => BOL_UserService::getInstance()->getDisplayName($userId),
            'url' => BOL_UserService::getInstance()->getUserUrl($userId)
        );

        $comment = BOL_CommentService::getInstance()->findComment($commentId);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));

        $event = new PEEP_Event('notifications.add', array(
            'pluginKey' => 'stories',
            'entityType' => 'stories-add_comment',
            'entityId' => (int) $comment->getId(),
            'action' => 'stories-add_comment',
            'userId' => $post->authorId,
            'time' => time()
        ), array(
            'avatar' => $avatars[$userId],
            'string' => array(
                'key' => 'stories+comment_notification_string',
                'vars' => array(
                    'actor' => $actor['name'],
                    'actorUrl' => $actor['url'],
                    'title' => $post->getTitle(),
                    'url' => PEEP::getRouter()->urlForRoute('post', array('id' => $post->getId()))
                )
            ),
            'content' => $comment->getMessage(),
            'url' => PEEP::getRouter()->urlForRoute('post', array('id' => $post->getId()))
        ));

        PEEP::getEventManager()->trigger($event);
    }

    public function onDeleteComment( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'story-post' )
            return;

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = (int) $params['commentId'];
    }

    public function onUnregisterUser( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['deleteContent']) )
        {
            return;
        }

        PEEP::getCacheManager()->clean(array(PostDao::CACHE_TAG_POST_COUNT));

        $userId = $params['userId'];

        $count = (int) $this->service->countUserPost($userId);

        if ( $count == 0 )
        {
            return;
        }

        $list = $this->service->findUserPostList($userId, 0, $count);

        foreach ( $list as $post )
        {
            $this->service->delete($post);
        }
    }

    public function onCollectEnabledAdsPages( BASE_CLASS_EventCollector $event )
    {
        $event->add('stories');
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $event->add(
            array(
                'stories' => array(
                    'label' => $language->text('stories', 'auth_group_label'),
                    'actions' => array(
                        'add' => $language->text('stories', 'auth_action_label_add'),
                        'view' => $language->text('stories', 'auth_action_label_view'),
                        'add_comment' => $language->text('stories', 'auth_action_label_add_comment')
                    )
                )
            )
        );
    }

    public function onCollectFeedConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $event->add(array(
            'label' => $language->text('stories', 'feed_content_label'),
            'activity' => '*:story-post'
        ));
    }

    public function onCollectFeedPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('*:story-post', PostService::PRIVACY_ACTION_VIEW_STORY_POSTS));
    }

    public function onCollectPrivacyActionList( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $action = array(
            'key' => PostService::PRIVACY_ACTION_VIEW_STORY_POSTS,
            'pluginKey' => 'stories',
            'label' => $language->text('stories', 'privacy_action_view_story_posts'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);

        $action = array(
            'key' => PostService::PRIVACY_ACTION_COMMENT_STORY_POSTS,
            'pluginKey' => 'stories',
            'label' => $language->text('stories', 'privacy_action_comment_story_posts'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function onChangeActionPrivacy( PEEP_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[PostService::PRIVACY_ACTION_VIEW_STORY_POSTS]) )
        {
            return;
        }

        PostService::getInstance()->updateStoriesPrivacy($userId, $actionList[PostService::PRIVACY_ACTION_VIEW_STORY_POSTS]);
    }

    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $userId = PEEP::getUser()->getId();
        $username = PEEP::getUser()->getUserObject()->getUsername();

        $postCount = (int) $this->service->countUserPost($userId);
        $draftCount = (int) $this->service->countUserDraft($userId);
        $count = $postCount + $draftCount;
        if ( $count > 0 )
        {
            if ( $postCount > 0 )
            {
                $url = PEEP::getRouter()->urlForRoute('story-manage-posts');
            }
            else if ( $draftCount > 0 )
            {
                $url = PEEP::getRouter()->urlForRoute('story-manage-drafts');
            }

            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => PEEP::getLanguage()->text('stories', 'my_story'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => PEEP::getRouter()->urlForRoute('user-story', array('user' => $username)),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url,
            ));
        }
    }

    public function onAddStoryPost( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'story-post' )
        {
            return;
        }

        $post = $this->service->findById($params['entityId']);

        $content = nl2br(UTIL_String::truncate(strip_tags($post->post), 150, '...'));
        $title = UTIL_String::truncate(strip_tags($post->title), 100, '...');

        $data = array(
            'time' => (int) $post->timestamp,
            'ownerId' => $post->authorId,
            'string'=>array("key" => "stories+feed_add_item_label"),
            'content' => array(
                'format' => 'content',
                'vars' => array(
                    'title' => $title,
                    'description' => $content,
                    'url' => array(
                        "routeName" => 'post',
                        "vars" => array('id' => $post->id)
                    ),
                    'iconClass' => 'peep_ic_story'
                )
            ),
            'view' => array(
                'iconClass' => 'peep_ic_write'
            )
        );

        $e->setData($data);
    }

    public function onUpdateStoryPost( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'story-post' )
        {
            return;
        }

        $post = $this->service->findById($params['entityId']);

        $content = nl2br(UTIL_String::truncate(strip_tags($post->post), 150, '...'));
        $title = UTIL_String::truncate(strip_tags($post->title), 100, '...');

        $data = array(
            'time' => (int) $post->timestamp,
            'ownerId' => $post->authorId,
            'string'=>array("key" => "stories+feed_add_item_label"),
            'content' => array(
                'format' => 'content',
                'vars' => array(
                    'title' => $title,
                    'description' => $content,
                    'url' => array(
                        "routeName" => 'post',
                        "vars" => array('id' => $post->id)
                    ),
                    'iconClass' => 'peep_ic_story'
                )
            ),
            'view' => array(
                'iconClass' => 'peep_ic_write'
            ),
            'actionDto' => $data['actionDto']
        );

        $e->setData($data);
    }

    public function onFeedAddComment( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'story-post' )
        {
            return;
        }

        $post = $this->service->findById($params['entityId']);
        $userId = $post->getAuthorId();

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        if ( $userId == $params['userId'] )
        {
            $string = array(
                'key'=>'stories+feed_activity_owner_post_string'
            );
        }
        else
        {
            $string = array(
                'key'=>'stories+feed_activity_post_string',
                'vars'=>array('user' => $userEmbed)
            );
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $params['commentId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'stories'
                ), array(
                'string' => $string
            )));
    }

    public function onFeedAddLike( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'story-post' )
        {
            return;
        }

        $post = $this->service->findById($params['entityId']);
        $userId = $post->getAuthorId();

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        if ( $userId == $params['userId'] )
        {
            $string = array(
                'key'=>'stories+feed_activity_owner_post_string_like'
            );
        }
        else
        {
            $string = array(
                'key'=>'stories+feed_activity_post_string_like',
                'vars'=>array('user' => $userEmbed)
            );
        }

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'stories'
                ), array(
                'string' => $string
            )));
    }

    public function sosialSharingGetStoryInfo( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'user_story' )
        {
            if( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('stories', 'view') )
            {
                $data['display'] = true;
            }

            $event->setData($data);
            return;
        }

        if ( $params['entityType'] == 'stories' )
        {
            $storytDto = PostService::getInstance()->findById($params['entityId']);

            $displaySocialSharing = true;

            try
            {
                $eventParams = array(
                    'action' => 'stories_view_story_posts',
                    'ownerId' => $storytDto->getAuthorId(),
                    'viewerId' => 0
                );

                PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $ex )
            {
                $displaySocialSharing = false;
            }


            if ( $displaySocialSharing && ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('stories', 'view') || $storytDto->isDraft() ) )
            {
                $displaySocialSharing = false;
            }

            if ( !empty($storytDto) )
            {
                $data['display'] = $displaySocialSharing;
            }

            $event->setData($data);
        }
    }
}