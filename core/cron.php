<?php

abstract class PEEP_Cron
{

    public function __construct()
    {
        $this->addJob('run', $this->getRunInterval());
    }
    private $jobs = array();

    /**
     * Add cron job
     *
     * @param string $methodName
     * @param int $runInterval in minutes
     */
    protected function addJob( $methodName, $runInterval = 1 )
    {
        $this->jobs[$methodName] = $runInterval;
    }

    public function getJobList()
    {
        return $this->jobs;
    }

    /**
     *  Return run interval in minutes
     *
     * @return int
     */
    public function getRunInterval()
    {
        return 1;
    }

    public abstract function run();
}