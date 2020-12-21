<?php

class BOL_Question extends PEEP_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $sectionName;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $presentation;
    /**
     * @var integer
     */
    public $required = 0;
    /**
     * @var integer
     */
    public $onJoin = 0;
    /**
     * @var integer
     */
    public $onEdit = 0;
    /**
     * @var integer
     */
    public $onSearch = 0;
    /**
     * @var integer
     */
    public $onView = 0;
    /**
     * @var integer
     */
    public $base = 0;
    /**
     * @var integer
     */
    public $removable = 1;
    /**
     * @var integer
     */
    public $sortOrder;
    /**
     * @var integer
     */
    public $columnCount = 1;
    /**
     * @var string
     */
    public $parent;
    /**
     * @var string
     */
    public $custom;
}

