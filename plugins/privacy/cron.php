<?php
class PRIVACY_Cron extends PEEP_Cron
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        PRIVACY_BOL_ActionService::getInstance()->cronUpdatePrivacy();
    }
}