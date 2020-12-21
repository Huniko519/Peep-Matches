<?php

class BOL_SearchEntity extends PEEP_Entity
{
    /**
     * Entity type
     * @var string
     */
    public $entityType;

    /**
     * Entity id
     * @var string
     */
    public $entityId;

    /**
     * Text
     * @var string
     */
    public $text;

    /**
     * Status
     * @var integer
     */
    public $status;

    /**
     * TimeStamp
     * @var integer
     */
    public $timeStamp;

    /**
     * Activated
     * @var integer
     */
    public $activated;
}