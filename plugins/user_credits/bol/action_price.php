<?php

class USERCREDITS_BOL_ActionPrice extends PEEP_Entity
{
    /**
     * @var int
     */
    public $actionId;
    /**
     * @var int
     */
    public $accountTypeId;
    /**
     * @var float
     */
    public $amount;
    /**
     * @var int
     */
    public $disabled = 0;
}