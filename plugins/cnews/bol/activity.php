<?php

class CNEWS_BOL_Activity extends PEEP_Entity
{
    /**
     * 
     * @var int
     */
    public $actionId;
    
    /**
     * 
     * @var int
     */
    public $userId;
    
    /**
     * 
     * @var string
     */
    public $activityType;
    
    /**
     * 
     * @var int
     */
    public $activityId;
    
    /**
     * 
     * @var string
     */
    public $data;
    
    /**
     * 
     * @var int
     */
    public $timeStamp;
    
     /**
     * 
     * @var int
     */
    public $visibility;
    
    /**
     * 
     * @var string
     */
    public $privacy;
    
    /**
     * 
     * @var string
     */
    public $status = CNEWS_BOL_Service::ACTION_STATUS_ACTIVE;
}