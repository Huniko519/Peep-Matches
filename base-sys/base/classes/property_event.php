<?php

class BASE_CLASS_PropertyEvent extends PEEP_Event
{
    protected $props;

    /**
     * Constructor.
     */
    public function __construct( $name, array $properties )
    {
        parent::__construct($name);
        $this->props = $properties;
    }

    public function getProperties()
    {
        return $this->props;
    }

    public function getProperty( $name )
    {
        return array_key_exists($name, $this->props) ? $this->props[$name] : null;
    }

    public function setProperty( $name, $val )
    {
        $this->props[$name] = $val;
    }
}