<?php

class BASE_CLASS_EventUserAction extends PEEP_Event
{
    private $plugin;
    private $action;
    private $userId;

    public function __construct( $name, $pluginKey, $action, $userId )
    {
        parent::__construct($name);

        $this->plugin = trim($pluginKey);
        $this->action = trim($action);
        $this->userId = (int) $userId;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getUserId()
    {
        return $this->action;
    }
}