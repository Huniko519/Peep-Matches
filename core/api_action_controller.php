<?php

abstract class PEEP_ApiActionController
{
    /**
     * List of assigned vars.
     *
     * @var array
     */
    protected $assignedVars = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        
    }

    /**
     * Assigns variable.
     *
     * @param string $name
     * @param mixed $value
     */
    public function assign( $name, $value )
    {
        $this->assignedVars[$name] = $value;
    }

    /**
     * @param string $varName
     */
    public function clearAssign( $varName )
    {
        if ( isset($this->assignedVars[$varName]) )
        {
            unset($this->assignedVars[$varName]);
        }
    }

    public function onBeforeRender()
    {

    }

    public function init()
    {
        
    }

    /**
     * Returns rendered markup.
     *
     * @return string
     */
    public function render()
    {
        return $this->assignedVars;
    }
}