<?php

class BOL_Invitation extends PEEP_Entity
{
    /**
     * @var string
     */
    public $entityType;

    /**
     * @var int
     */
    public $entityId;

    /**
     * @var int
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $pluginKey;

    /**
     * @var int
     */
    public $timeStamp;

    /**
     *
     * @var int
     */
    public $viewed = false;

    /**
     *
     * @var int
     */
    public $sent = false;

    /**
     *
     * @var int
     */
    public $active = true;

    /**
     *
     * @var string
     */
    public $action;

    /**
     * @var data
     */
    public $data;

    public function setData( $data )
    {
        $this->data = json_encode($data);
    }

    public function getData()
    {
        return empty($this->data) ? null : json_decode($this->data, true);
    }
}
