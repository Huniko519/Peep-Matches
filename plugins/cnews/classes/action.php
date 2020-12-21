<?php

class CNEWS_CLASS_Action
{
    private $activity = array();
    private $properties = array();
    private $createActivity, $lastActivity;
    private $creatorIdList = array();
    private $feeds = array();

    public function setDataValue( $name, $value )
    {
        $this->properties['data'][$name] = $value;
    }

    public function getDataValue( $name )
    {
        if ( empty($this->properties['data'][$name]) )
        {
            return null;
        }

        return $this->properties['data'][$name];
    }

    public function getData()
    {
        return $this->properties['data'];
    }

    public function setData( $data )
    {
        $this->properties['data'] = $data;
    }

    public function setEntity( $entityType, $entityId )
    {
        $this->properties['entityType'] = $entityType;
        $this->properties['entityId'] = $entityId;
    }

    /**
     *
     * @return CNEWS_CLASS_Identifier
     */
    public function getEntity()
    {
        if ( empty($this->properties['entityType']) || empty($this->properties['entityId']) )
        {
            return null;
        }

        return new CNEWS_CLASS_Identifier($this->properties['entityType'], $this->properties['entityId']);
    }

    public function setCreateTime( $time )
    {
        $this->properties['createTime'] = $time;
    }

    public function getCreateTime()
    {
        return empty($this->properties['createTime']) ? null : (int) $this->properties['createTime'];
    }

    public function setUserId( $userId )
    {
        $this->properties['userId'] = $userId;
    }

    public function getUserId()
    {
        return (int) $this->properties['userId'];
    }
    
    public function getCreatorIdList()
    {
        return $this->creatorIdList;
    }
    
    public function setFeedList( $feedList )
    {
        $this->feeds = $feedList;
    }
    
    public function getFeedList()
    {
        return $this->feeds;
    }

    public function getUpdateTime()
    {
        return (int) $this->getLastActivity()->timeStamp;
    }

    public function setId( $id )
    {
        return $this->properties['id'] = $id;
    }

    public function getId()
    {
        return $this->properties['id'];
    }

    public function setPluginKey( $key )
    {
        $this->properties['pluginKey'] = $key;
    }

    public function getPluginKey()
    {
        return $this->properties['pluginKey'];
    }
    
    public function getFormat()
    {
        return $this->properties['format'];
    }
    
    public function setFormat( $format )
    {
        return $this->properties['format'] = $format;
    }

    public function getActivityList( $type = null )
    {
        if ( $type === null )
        {
            return $this->activity;
        }
        
        $out = array();
        foreach ( $this->activity as $activity )
        {
            /* @var $activity CNEWS_BOL_Activity */
            if ( $activity->activityType == $type )
            {
                $out[] = $activity;
            }
        }

        return $out;
    }

    /**
     *
     * @return CNEWS_BOL_Activity
     */
    public function getCreateActivity()
    {
        return $this->createActivity;
    }
    
    /**
     *
     * @return CNEWS_BOL_Activity
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    public function setActivityList( array $list )
    {
        $this->activity = $list;

        $createActivityList = $this->getActivityList(CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE);
        
        foreach ( array_reverse($createActivityList) as $a )
        {
            $this->creatorIdList[] = $a->userId;
        }
        
        $this->createActivity = end($createActivityList);
        $this->lastActivity = reset($this->activity);
        $this->setCreateTime($this->createActivity->timeStamp);
        $this->setUserId($this->createActivity->userId);
    }
    
    /**
     *
     * @param $type
     * @param $id
     * @return CNEWS_BOL_Activity
     */
    public function getActivity( $type, $id = null )
    {
        $activities = $this->getActivityList($type);
        
        if ( empty($id) )
        {
            return end($activities);
        }
        
        $activity = null;
        
        foreach ( $activities as $a )
        {
            /* @var $a CNEWS_BOL_Activity */
            if ( $a->id == $id )
            {
                $activity = $a;
            }
        }

        return $activity;
    }
}