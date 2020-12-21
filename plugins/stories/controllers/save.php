<?php

class STORIES_CTRL_Save extends PEEP_ActionController
{

    public function index( $params = array() )
    {
        if (PEEP::getRequest()->isAjax())
        {
            exit();
        }

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $plugin = PEEP::getPluginManager()->getPlugin('stories');
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'stories', 'main_menu_item');


        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'save_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_write');

        if ( !PEEP::getUser()->isAuthorized('stories', 'add') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('stories', 'add_story');
            throw new AuthorizationException($status['msg']);

            return;
        }

        $this->assign('authMsg', null);

        $id = empty($params['id']) ? 0 : $params['id'];

        $service = PostService::getInstance(); /* @var $service PostService */

        $tagService = BOL_TagService::getInstance();

        if ( intval($id) > 0 )
        {
            $post = $service->findById($id);

            if ($post->authorId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized('stories'))
            {
                throw new Redirect404Exception();
            }

            $eventParams = array(
                'action' => PostService::PRIVACY_ACTION_VIEW_STORY_POSTS,
                'ownerId' => $post->authorId
            );

            $privacy = PEEP::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);
            if (!empty($privacy))
            {
                $post->setPrivacy($privacy);
            }

        }
        else
        {
            $post = new Post();

            $eventParams = array(
                'action' => PostService::PRIVACY_ACTION_VIEW_STORY_POSTS,
                'ownerId' => PEEP::getUser()->getId()
            );

            $privacy = PEEP::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);
            if (!empty($privacy))
            {
                $post->setPrivacy($privacy);
            }

            $post->setAuthorId(PEEP::getUser()->getId());
        }

        $form = new SaveForm($post);

        if ( PEEP::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('draft', 'publish')) ) && $form->isValid($_POST) )
        {
            $form->process($this);
            PEEP::getApplication()->redirect(PEEP::getRouter()->urlForRoute('post-save-edit', array('id' => $post->getId())));
        }

        $this->addForm($form);

        $this->assign('info', array('dto' => $post));

        PEEP::getDocument()->setTitle(PEEP::getLanguage()->text('stories', 'meta_title_new_story_post'));
        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('stories', 'meta_description_new_story_post'));

    }

    public function delete( $params )
    {
        if (PEEP::getRequest()->isAjax() || !PEEP::getUser()->isAuthenticated())
        {
            exit();
        }
        /*
          @var $service PostService
         */
        $service = PostService::getInstance();

        $id = $params['id'];

        $dto = $service->findById($id);

        if ( !empty($dto) )
        {
            if ($dto->authorId == PEEP::getUser()->getId() || PEEP::getUser()->isAuthorized('stories'))
            {
                PEEP::getEventManager()->trigger(new PEEP_Event(PostService::EVENT_BEFORE_DELETE, array(
                    'postId' => $id
                )));
                $service->delete($dto);
                PEEP::getEventManager()->trigger(new PEEP_Event(PostService::EVENT_AFTER_DELETE, array(
                    'postId' => $id
                )));
            }
        }

        if ( !empty($_GET['back-to']) )
        {
            $this->redirect($_GET['back-to']);
        }

        $author = BOL_UserService::getInstance()->findUserById($dto->authorId);

        $this->redirect(PEEP::getRouter()->urlForRoute('user-story', array('user' => $author->getUsername())));
    }
}

class SaveForm extends Form
{
    /**
     *
     * @var Post
     */
    private $post;
    /**
     *
     * @var type PostService
     */
    private $service;


    public function __construct( Post $post, $tags = array() )
    {
        parent::__construct('save');

        $this->service = PostService::getInstance();

        $this->post = $post;

        $this->setMethod('post');

        $titleTextField = new TextField('title');

        $this->addElement($titleTextField->setLabel(PEEP::getLanguage()->text('stories', 'save_form_lbl_title'))->setValue($post->getTitle())->setRequired(true));

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_MORE,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
            BOL_TextFormatService::WS_BTN_VIDEO
        );

        $postTextArea = new WysiwygTextarea('post', $buttons);
        $postTextArea->setSize(WysiwygTextarea::SIZE_L);
        $postTextArea->setLabel(PEEP::getLanguage()->text('stories', 'save_form_lbl_post'));
        $postTextArea->setValue($post->getPost());
        $postTextArea->setRequired(true);
        $this->addElement($postTextArea);

        $draftSubmit = new Submit('draft');
        $draftSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'draft');");

        if ( $post->getId() != null && !$post->isDraft() )
        {
            $text = PEEP::getLanguage()->text('stories', 'change_status_draft');
        }
        else
        {
            $text = PEEP::getLanguage()->text('stories', 'sava_draft');
        }

        $this->addElement($draftSubmit->setValue($text));

        if ( $post->getId() != null && !$post->isDraft() )
        {
            $text = PEEP::getLanguage()->text('stories', 'update');
        }
        else
        {
            $text = PEEP::getLanguage()->text('stories', 'save_publish');
        }

        $publishSubmit = new Submit('publish');
        $publishSubmit->addAttribute('onclick', "$('#save_post_command').attr('value', 'publish');");

        $this->addElement($publishSubmit->setValue($text));

        $tagService = BOL_TagService::getInstance();

        $tags = array();

        if ( intval($this->post->getId()) > 0 )
        {
            $arr = $tagService->findEntityTags($this->post->getId(), 'story-post');

            foreach ( (!empty($arr) ? $arr : array() ) as $dto )
            {
                $tags[] = $dto->getLabel();
            }
        }

        $tf = new TagsInputField('tf');
        $tf->setLabel(PEEP::getLanguage()->text('stories', 'tags_field_label'));
        $tf->setValue($tags);

        $this->addElement($tf);
    }

    public function process( $ctrl )
    {
        PEEP::getCacheManager()->clean( array( PostDao::CACHE_TAG_POST_COUNT ));

        $service = PostService::getInstance(); /* @var $postDao PostService */

        $data = $this->getValues();

        $data['title'] = UTIL_HtmlTag::stripJs($data['title']);

        $postIsNotPublished = $this->post->getStatus() == 2;

        $text = UTIL_HtmlTag::sanitize($data['post']);

        /* @var $post Post */
        $this->post->setTitle($data['title']);
        $this->post->setPost($text);
        $this->post->setIsDraft($_POST['command'] == 'draft');

        $isCreate = empty($this->post->id);
        if ( $isCreate )
        {
            $this->post->setTimestamp(time());
            //Required to make #698 and #822 work together
            if ($_POST['command'] == 'draft')
            {
                $this->post->setIsDraft(2);
            }

            BOL_AuthorizationService::getInstance()->trackAction('stories', 'add_story');
        }
        else
        {
            //If post is not new and saved as draft, remove their item from newsfeed
            if ($_POST['command'] == 'draft')
            {
                PEEP::getEventManager()->trigger(new PEEP_Event('feed.delete_item', array('entityType' => 'story-post', 'entityId' => $this->post->id)));
            }
            else if($postIsNotPublished)
            {
                // Update timestamp if post was published for the first time
                $this->post->setTimestamp(time());
            }

        }

        $service->save($this->post);

        $tags = array();
        if ( intval($this->post->getId()) > 0 )
        {
            $tags = $data['tf'];
            foreach ($tags as $id => $tag)
            {
                $tags[$id] = UTIL_HtmlTag::stripTags($tag);
            }
        }
        $tagService = BOL_TagService::getInstance();
        $tagService->updateEntityTags($this->post->getId(), 'story-post', $tags );

        if ($this->post->isDraft())
        {
            $tagService->setEntityStatus('story-post', $this->post->getId(), false);

            if ($isCreate)
            {
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'create_draft_success_msg'));
            }
            else
            {
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'edit_draft_success_msg'));
            }
        }
        else
        {
            $tagService->setEntityStatus('story-post', $this->post->getId(), true);

            //Newsfeed
            $event = new PEEP_Event('feed.action', array(
                'pluginKey' => 'stories',
                'entityType' => 'story-post',
                'entityId' => $this->post->getId(),
                'userId' => $this->post->getAuthorId(),
            ));
            PEEP::getEventManager()->trigger($event);

            if ($isCreate)
            {
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'create_success_msg'));

                PEEP::getEventManager()->trigger(new PEEP_Event(PostService::EVENT_AFTER_ADD, array(
                    'postId' => $this->post->getId()
                )));
            }
            else
            {
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'edit_success_msg'));
                PEEP::getEventManager()->trigger(new PEEP_Event(PostService::EVENT_AFTER_EDIT, array(
                    'postId' => $this->post->getId()
                )));
            }

            $ctrl->redirect(PEEP::getRouter()->urlForRoute('post', array('id' => $this->post->getId())));
        }
    }
}

?>
