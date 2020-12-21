<?php


$widget = BOL_ComponentAdminService::getInstance()->addWidget('SPOTLIGHT_CMP_IndexWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_TOP, 0 );

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new SPOTLIGHT_CLASS_Credits();
$credits->triggerCreditActionsAdd();
