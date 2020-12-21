<?php

class PVISITORS_Cron extends PEEP_Cron
{    
    public function __construct()
    {
        parent::__construct();

        $this->addJob('visitorsCheckProcess', 60);
    }

    public function run() { }

    public function visitorsCheckProcess()
    {
        PVISITORS_BOL_Service::getInstance()->checkExpiredVisitors();
    }
}