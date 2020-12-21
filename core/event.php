<?php

class PEEP_Event
{
    /**
     * Event name.
     *
     * @var string
     */
    protected $name;
    /**
     * Event processed value.
     *
     * @var mixed
     */
    protected $data;
    /**
     * Event call params.
     *
     * @var array
     */
    protected $params;
    /**
     * @var bool
     */
    protected $stop = false;

    /**
     * Constructor.
     */
    public function __construct( $name, array $params = array(), $dataValue = null )
    {
        $this->name = trim($name);
        $this->params = $params;
        $this->data = $dataValue;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }

    /**
     *
     */
    public function stopPropagation()
    {
        $this->stop = true;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stop;
    }
}