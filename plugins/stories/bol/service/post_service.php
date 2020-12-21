<?php

class PostService
{
    const FEED_ENTITY_TYPE = 'story-post';
    const PRIVACY_ACTION_VIEW_STORY_POSTS = 'stories_view_story_posts';
    const PRIVACY_ACTION_COMMENT_STORY_POSTS = 'stories_comment_story_posts';

    const POST_STATUS_PUBLISHED = 0;
    const POST_STATUS_DRAFT = 1;
    const POST_STATUS_DRAFT_WAS_NOT_PUBLISHED = 2;
    const POST_STATUS_APPROVAL = 3;

    const EVENT_AFTER_DELETE = 'stories.after_delete';
    const EVENT_BEFORE_DELETE = 'stories.before_delete';
    const EVENT_AFTER_EDIT = 'stories.after_edit';
    const EVENT_AFTER_ADD = 'stories.after_add';

    /*
     * @var STORY_BOL_StoryService
     */
    private static $classInstance;

    /**
     * @var array
     */
    private $config = array();

    /*
      @var PostDao
     */
    private $dao;

    private function __construct()
    {
        $this->dao = PostDao::getInstance();

        $this->config['allowedMPElements'] = array();
    }

    public function getConfig()
    {
        return $this->config;
    }

        /**
     * Returns class instance
     *
     * @return PostService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function save( $dto )
    {
        $dao = $this->dao;

        return $dao->save($dto);
    }

    /**
     * @return Post
     */
    public function findById( $id )
    {
        $dao = $this->dao;

        return $dao->findById($id);
    }

    //<USER-STORY>

    private function deleteByAuthorId( $userId ) // do not use it!!
    {
        //$this->dao->deleteByAuthorId($userId);
    }
    /*
     * $which can take on of two following 'next', 'prev' values
     */

    public function findAdjacentUserPost( $id, $postId, $which )
    {
        return $this->dao->findAdjacentUserPost($id, $postId, $which);
    }

    public function findUserPostList( $userId, $first, $count )
    {
        return $this->dao->findUserPostList($userId, $first, $count);
    }

    public function findUserDraftList( $userId, $first, $count )
    {
        return $this->dao->findUserDraftList($userId, $first, $count);
    }

    public function countUserPost( $userId )
    {
        return $this->dao->countUserPost($userId);
    }

    public function countUserPostComment( $userId )
    {
        return $this->dao->countUserPostComment($userId);
    }

    public function countUserDraft( $userId )
    {
        return $this->dao->countUserDraft($userId);
    }

    public function findUserPostCommentList( $userId, $first, $count )
    {
        return $this->dao->findUserPostCommentList($userId, $first, $count);
    }

    public function findUserLastPost( $userId )
    {
        return $this->dao->findUserLastPost($userId);
    }

    public function findUserArchiveData( $id )
    {
        return $this->dao->findUserArchiveData($id);
    }

    public function findUserPostListByPeriod( $id, $lb, $ub, $first, $count )
    {
        return $this->dao->findUserPostListByPeriod($id, $lb, $ub, $first, $count);
    }

    public function countUserPostByPeriod( $id, $lb, $ub )
    {
        return $this->dao->countUserPostByPeriod($id, $lb, $ub);
    }

    //</USER-STORY>
    //<SITE-STORY>
    public function findList( $first, $count )
    {
        return $this->dao->findList($first, $count);
    }

    public function countAll()
    {
        return $this->dao->countAll();
    }

    public function countPosts()
    {
        return $this->dao->countPosts();
    }

    public function findTopRatedList( $first, $count )
    {
        return $this->dao->findTopRatedList($first, $count);
    }

    public function findListByTag( $tag, $first, $count )
    {
        return $this->dao->findListByTag($tag, $first, $count);
    }

    public function countByTag( $tag )
    {
        return $this->dao->countByTag($tag);
    }

    public function delete( Post $dto )
    {
        $this->deletePost($dto->getId());
    }

    //</SITE-STORY>

    public function findListByIdList( $list )
    {
        return $this->dao->findListByIdList($list);
    }

    public function onAuthorSuspend( PEEP_Event $event )
    {
        $params = $event->getParams();
    }

    /**
     * Get set of allowed tags for stories
     *
     * @return array
     */
    public function getAllowedHtmlTags()
    {
        return array("object", "embed", "param", "strong", "i", "u", "a", "!--more--", "img", "blockquote", "span", "pre", "iframe");
    }

    public function updateStoriesPrivacy( $userId, $privacy )
    {
        $count = $this->countUserPost($userId);
        $entities = PostService::getInstance()->findUserPostList($userId, 0, $count);
        $entityIds = array();

        foreach ($entities as $post)
        {
            $entityIds[] = $post->getId();
        }

        $status = ( $privacy == 'everybody' ) ? true : false;

        $event = new PEEP_Event('base.update_entity_items_status', array(
            'entityType' => 'story-post',
            'entityIds' => $entityIds,
            'status' => $status,
        ));
        PEEP::getEventManager()->trigger($event);

        $this->dao->updateStoriesPrivacy( $userId, $privacy );
        PEEP::getCacheManager()->clean( array( PostDao::CACHE_TAG_POST_COUNT ));
    }

    public function processPostText($text)
    {
        $text = str_replace('&nbsp;', ' ', $text);
        $text = strip_tags($text);
        return $text;
    }

    public function findUserNewCommentCount($userId)
    {
        return $this->dao->countUserPostNewComment($userId);
    }

    public function deletePost($postId)
    {
        BOL_CommentService::getInstance()->deleteEntityComments('story-post', $postId);
        BOL_RateService::getInstance()->deleteEntityRates($postId, 'story-post');
        BOL_TagService::getInstance()->deleteEntityTags($postId, 'story-post');
        BOL_FlagService::getInstance()->deleteByTypeAndEntityId(STORIES_CLASS_ContentProvider::ENTITY_TYPE, $postId);

        PEEP::getCacheManager()->clean( array( PostDao::CACHE_TAG_POST_COUNT ));

        PEEP::getEventManager()->trigger(new PEEP_Event('feed.delete_item', array('entityType' => 'story-post', 'entityId' => $postId)));

        $this->dao->deleteById($postId);
    }

    public function findPostListByIds($postIds)
    {
        return $this->dao->findByIdList($postIds);
    }

    public function getPostUrl($post)
    {
        return PEEP::getRouter()->urlForRoute('post', array('id'=>$post->getId()));
    }
}