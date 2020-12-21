<?php

class BASE_CLASS_EventCollector extends PEEP_Event
{
    public function __construct( $name, $params = array() )
    {
        parent::__construct($name, $params);

        $this->data = array();
    }

    public function add( $item )
    {
        $this->data[] = $item;
    }

    public function setData( $data )
    {
        throw new LogicException("Can't set data in collector event `" . $this->getName() . "`!");
    }
}