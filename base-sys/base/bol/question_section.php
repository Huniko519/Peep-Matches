<?php

class BOL_QuestionSection extends PEEP_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var integer
     */
    public $sortOrder;
    
    /**
     * @var int
     */
    public $isHidden = false;
    
    /**
     * @var int
     */
    public $isDeletable = true;
}