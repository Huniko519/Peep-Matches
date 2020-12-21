<?php

class BOL_Preference extends PEEP_Entity
{
    /**
     * @var string
     */
    public $key;
    
    /**
     * @var string
     */
    public $sectionName;
    
    /**
     * @var string
     */
    public $defaultValue;
    
    /**
     * @var int
     */
    public $sortOrder;
}
