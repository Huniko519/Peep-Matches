<?php

class BOL_DbCache extends PEEP_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $value;
    /**
     * 
     * @var int
     */
    public $expireStamp;
}
