<?php

// credits
require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new MAILBOX_CLASS_Credits();
$credits->triggerCreditActionsAdd();