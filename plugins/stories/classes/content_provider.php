<?php

class STORIES_CLASS_ContentProvider
{
    const ENTITY_TYPE = PostService::FEED_ENTITY_TYPE;

    /**
     * Singleton instance.
     *
     * @var STORIES_CLASS_ContentProvider
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return STORIES_CLASS_ContentProvider
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

    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "pluginKey" => "stories",
            "group" => "stories",
            "groupLabel" => PEEP::getLanguage()->text("stories", "content_stories_label"),
            "entityType" => self::ENTITY_TYPE,
            "entityLabel" => PEEP::getLanguage()->text("stories", "content_story_label"),
            "displayFormat" => "content"
        ));
    }

    public function onGetInfo( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }

        $posts = $this->service->findPostListByIds($params["entityIds"]);
        $out = array();
        /**
         * @var Post $post
         */
        foreach ( $posts as $post )
        {
            $info = array();

            $info["id"] = $post->id;
            $info["userId"] = $post->authorId;
            $info["title"] = $post->title;
            $info["description"] = $post->post;
            $info["url"] = $this->service->getPostUrl($post);
            $info["timeStamp"] = $post->timestamp;

            $out[$post->id] = $info;
        }

        $event->setData($out);

        return $out;
    }

    public function onUpdateInfo( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }

        foreach ( $data as $postId => $info )
        {
            $status = $info["status"] == BOL_ContentService::STATUS_APPROVAL ? PostService::POST_STATUS_APPROVAL : PostService::POST_STATUS_PUBLISHED;

            $entityDto = $this->service->findById($postId);
            $entityDto->isDraft = $status;

            $this->service->save($entityDto);

            // Set tags status
            $tagActive = ($info["status"] == BOL_ContentService::STATUS_APPROVAL) ? false : true;
            BOL_TagService::getInstance()->setEntityStatus(self::ENTITY_TYPE, $postId, $tagActive);
        }
    }

    public function onDelete( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }

        foreach ( $params["entityIds"] as $postId )
        {
            $this->service->deletePost($postId);
        }
    }

    public function onBeforePostDelete( PEEP_Event $event )
    {
        $params = $event->getParams();

        PEEP::getEventManager()->trigger(new PEEP_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => self::ENTITY_TYPE,
            "entityId" => $params["postId"]
        )));
    }

    public function onAfterPostAdd( PEEP_Event $event )
    {
        $params = $event->getParams();

        PEEP::getEventManager()->trigger(new PEEP_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
            "entityType" => self::ENTITY_TYPE,
            "entityId" => $params["postId"]
        ), array(
            "string" => array("key" => "stories+feed_add_item_label")
        )));
    }

    public function onAfterPostEdit( PEEP_Event $event )
    {
        $params = $event->getParams();

        PEEP::getEventManager()->trigger(new PEEP_Event(BOL_ContentService::EVENT_AFTER_CHANGE, array(
            "entityType" => self::ENTITY_TYPE,
            "entityId" => $params["postId"]
        ), array(
            "string" => array("key" => "stories+feed_edit_item_label")
        )));
    }

    public function init()
    {
        PEEP::getEventManager()->bind(PostService::EVENT_BEFORE_DELETE, array($this, "onBeforePostDelete"));
        PEEP::getEventManager()->bind(PostService::EVENT_AFTER_ADD, array($this, "onAfterPostAdd"));
        PEEP::getEventManager()->bind(PostService::EVENT_AFTER_EDIT, array($this, "onAfterPostEdit"));

        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, array($this, "onCollectTypes"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, array($this, "onGetInfo"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, array($this, "onUpdateInfo"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, array($this, "onDelete"));
    }
}