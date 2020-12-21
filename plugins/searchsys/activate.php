<?php

$cmpService = BOL_ComponentAdminService::getInstance();
require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new SEARCHSYS_CLASS_Credits();
$credits->triggerCreditActionAdd();