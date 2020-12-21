<?php

class BOL_QuestionData extends PEEP_Entity
{
    /**
     * @var int
     */
    public $questionName;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $textValue = '';
    /**
     * @var integer
     */
    public $intValue = 0;
    /**
     * @var integer
     */
    public $dateValue;
}