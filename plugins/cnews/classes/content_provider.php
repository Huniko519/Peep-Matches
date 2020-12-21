<?php

class CNEWS_CLASS_ContentProvider
{
    const ENTITY_TYPE_USER_STATUS = "user-status";
    
    /**
     * Singleton instance.
     *
     * @var CNEWS_CLASS_ContentProvider
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_CLASS_ContentProvider
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
     * @var CNEWS)BOL_Service
     */
    private $service;
    
    private function __construct()
    {
        $this->service = CNEWS_BOL_Service::getInstance();
    }
    
    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "pluginKey" => "cnews",
            "group" => "cnews",
            "groupLabel" => PEEP::getLanguage()->text("cnews", "content_group_label"),
            "entityType" => self::ENTITY_TYPE_USER_STATUS,
            "entityLabel" => PEEP::getLanguage()->text("cnews", "content_status_label"),
            "moderation" => array(BOL_ContentService::MODERATION_TOOL_FLAG)
        ));
    }
    
    public function onGetInfo( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE_USER_STATUS )
        {
            return;
        }
        
        $out = array();
        foreach ( $params["entityIds"] as $entityId )
        {
            $entity = $this->service->findAction($params["entityType"], $entityId);
            $data = json_decode($entity->data, true);
            
            $cActivities = $this->service->findActivity( CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $entity->id);
            $cActivity = reset($cActivities);

            $userId = null;
            $timeStamp = null;
            
            /* @var $cActivity CNEWS_BOL_Activity */
            if ( !empty($cActivity) )
            {
                $userId = $cActivity->userId;
                $timeStamp = $cActivity->timeStamp;
            }
            
            $info = array();

            $info["id"] = $entityId;
            $info["userId"] = $userId;
            $info["timeStamp"] = $timeStamp;

            $content = empty($data["content"]) ? null : $data["content"];
            
            if ( $content === null )
            {
                $info["text"] = $data["data"]["status"];
            }
            else if ($content["format"] == "text")
            {
                $info["text"] = $content["vars"]["status"];
            }
            else
            {
                $info["title"] = $content["vars"]["title"];
                $info["description"] = $content["vars"]["description"];
                $info["url"] = $content["vars"]["url"];
                
                if ( !empty($content["vars"]["status"]) )
                {
                    $info["text"] = $content["vars"]["status"];
                }
                
                if ( !empty($content["vars"]["embed"]) )
                {
                    $info["html"] = $content["vars"]["embed"];
                }
                
                $info["image"] = array();
                
                if ( !empty($content["vars"]["thumbnail"]) )
                {
                    $info["image"]["thumbnail"] = $content["vars"]["thumbnail"];
                }
                
                if ( !empty($content["vars"]["image"]) )
                {
                    $info["image"]["view"] = $content["vars"]["image"];
                }
            }
            
            $out[$entityId] = $info;
        }
        
        $event->setData($out);
        
        return $out;
    }
    
    public function onUpdateInfo( PEEP_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        $entityType = $params["entityType"];
        
        foreach ( $data as $entityId => $info )
        {
            $status = $info["status"] == BOL_ContentService::STATUS_ACTIVE
                    ? CNEWS_BOL_Service::ACTION_STATUS_ACTIVE
                    : CNEWS_BOL_Service::ACTION_STATUS_INACTIVE;
            
            $cActivities = $this->service->findActivity(CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE . ':' . $entityType . '.' . $entityId);
            
            foreach ( $cActivities as $activity )
            {
                $activity->status = $status;
                $this->service->saveActivity($activity);
            }
        }
    }
    
    public function onDelete( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE_USER_STATUS )
        {
            return;
        }

        foreach ( $params["entityIds"] as $entityId )
        {
            $this->service->removeAction($params["entityType"], $entityId);
        }
    }

    // Video events
    
    public function onBeforeActionDelete( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE_USER_STATUS )
        {
            return;
        }
        
        PEEP::getEventManager()->trigger(new PEEP_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => $params["entityType"],
            "entityId" => $params["entityType"]
        )));
    }
    
    public function onAfterActionAdd( PEEP_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE_USER_STATUS )
        {
            return;
        }
        
        PEEP::getEventManager()->trigger(new PEEP_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
            "entityType" => $params["entityType"],
            "entityId" => $params["entityId"]
        ), array(
            "string" => array("key" => "cnews+status_add_string")
        )));
    }
        
    public function init()
    {
        PEEP::getEventManager()->bind(CNEWS_BOL_Service::EVENT_BEFORE_ACTION_DELETE, array($this, "onBeforeActionDelete"));
        PEEP::getEventManager()->bind(CNEWS_BOL_Service::EVENT_AFTER_ACTION_ADD, array($this, "onAfterActionAdd"));
        
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, array($this, "onCollectTypes"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, array($this, "onGetInfo"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, array($this, "onUpdateInfo"));
        PEEP::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, array($this, "onDelete"));
    }
}