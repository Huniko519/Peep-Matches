<?php

class UTIL_Profiler
{
    /**
     * @var array
     */
    private static $classInstances;

    /**
     * @var int
     */
    private $checkPoints;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $result;

    /**
     * @var integer
     */
    private $chkCounter;

    /**
     * Returns profiler result array
     * 
     * @return array
     */
    public function getResult()
    {
        $this->stop();
        return $this->result;
    }

    /**
     * Returns total time past from the start.
     *
     * @return float
     */
    public function getTotalTime()
    {
        return (microtime(true) - $this->checkPoints['start']);
    }

    /**
     * Constructor
     *
     * @param string
     */
    private function __construct( $key )
    {
        $this->key = $key;
        $this->reset();
    }

    /**
     * Returns "single-tone" instance of class for every $key
     *
     * @param string $key #Profiler object identifier#
     * @return UTIL_Profiler
     */
    public static function getInstance( $key = '_peep_' )
    {
        if ( self::$classInstances === null )
        {
            self::$classInstances = array();
        }

        if ( !isset(self::$classInstances[$key]) )
        {
            self::$classInstances[$key] = new self($key);
        }

        return self::$classInstances[$key];
    }

    /**
     * Sets new profiler checkpoint
     *
     * @param string $key
     */
    public function mark( $key = null )
    {
        $this->checkPoints[( $key === null ? 'chk' . $this->chkCounter++ : $key)] = microtime(true);
    }

    /**
     * Stops profiler and geberates result array
     */
    private function stop()
    {
        $this->result['marks'] = array();

        foreach ( $this->checkPoints as $key => $value )
        {
            $this->result['marks'][$key] = sprintf('%.3f', $value - $this->checkPoints['start']);
        }

        $endMark = $this->result['marks']['end'] = sprintf('%.3f', microtime(true) - $this->checkPoints['start']);

        $this->result['total'] = $endMark;
    }

    /**
     * Resets profiler
     *
     */
    public function reset()
    {
        $this->checkPoints = array();
        $this->checkPoints['start'] = microtime(true);
        $this->result = array();
        $this->chkCounter = 0;
    }
}