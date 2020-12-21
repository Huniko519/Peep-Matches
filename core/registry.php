<?php

class PEEP_Registry
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var array
     */
    private $arrayData;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->data = array();
        $this->arrayData = array();
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Registry
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Registry
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function set( $key, $value )
    {
        $this->data[$key] = $value;
    }

    public function get( $key )
    {
        if ( !isset($this->data[$key]) )
        {
            return null;
        }

        return $this->data[$key];
    }

    public function setArray( $key, array $value )
    {
        $this->arrayData[$key] = $value;
    }

    public function addToArray( $key, $value )
    {
        if ( !isset($this->arrayData[$key]) )
        {
            $this->arrayData[$key] = array();
        }

        $this->arrayData[$key][] = $value;
    }

    public function getArray( $key )
    {
        if ( !isset($this->arrayData[$key]) )
        {
            return array();
        }

        return $this->arrayData[$key];
    }
}