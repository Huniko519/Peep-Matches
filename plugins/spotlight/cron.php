<?php

class SPOTLIGHT_Cron extends PEEP_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('clearExpiredUsers', 60);

    }

    public function run()
    {

    }

    public function clearExpiredUsers()
    {
        SPOTLIGHT_BOL_Service::getInstance()->clearExpiredUsers();
    }

}