<?php

class CNEWS_BOL_Follow extends PEEP_Entity
{
    /**
     * 
     * @var int
     */
    public $feedId;
    
    /**
     * 
     * @var string
     */
    public $feedType;
    
    /**
     * 
     * @var int
     */
    public $userId;
    
    /**
     * 
     * @var string
     */
    public $permission;
    
    /**
     * 
     * @var int
     */
    public $followTime;
}