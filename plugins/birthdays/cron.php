<?php

class BIRTHDAYS_Cron extends PEEP_Cron
{

    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        BIRTHDAYS_BOL_Service::getInstance()->checkBirthdays();
    }
}