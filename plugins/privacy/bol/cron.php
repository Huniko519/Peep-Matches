<?php

class PRIVACY_BOL_Cron extends PEEP_Entity
{
    /**
     * @var int
     */
    public $userId;
    
    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $value;

    /**
     * @var boolean
     */
    public $inProcess = 0;

    /**
     * @var int
     */
    public $timeStamp = 0;
}
