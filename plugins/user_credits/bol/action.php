<?php

class USERCREDITS_BOL_Action extends PEEP_Entity
{
    /**
     * @var string
     */
    public $pluginKey;
    /**
     * @var string
     */
    public $actionKey;
    /**
     * @var int
     */
    public $isHidden = 0;
    /**
     * @var string
     */
    public $settingsRoute;
    /**
     * @var int
     */
    public $active = 1;
}