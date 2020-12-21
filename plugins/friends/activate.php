<?php


//--
$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRIENDS_CMP_UserWidget');
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP,0);


require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new FRIENDS_CLASS_Credits();
$credits->triggerCreditActionsAdd();