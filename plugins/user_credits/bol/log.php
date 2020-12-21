<?php

class USERCREDITS_BOL_Log extends PEEP_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $actionId;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var int
     */
    public $logTimestamp;
    
    /**
     * @var string
     */
    public $additionalParams;
}