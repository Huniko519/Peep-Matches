<?php


$widget = array();

//--
$widget['user'] = BOL_ComponentAdminService::getInstance()->addWidget('STORIES_CMP_UserStoryWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['user'], BOL_ComponentAdminService::PLACE_PROFILE);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT );

//--
$widget['site'] = BOL_ComponentAdminService::getInstance()->addWidget('STORIES_CMP_StoryWidget', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget['site'], BOL_ComponentAdminService::PLACE_DASHBOARD);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT );

PEEP::getNavigation()->addMenuItem(PEEP_Navigation::MAIN, 'stories', 'stories', 'main_menu_item', PEEP_Navigation::VISIBLE_FOR_ALL);

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new STORIES_CLASS_Credits();
$credits->triggerCreditActionsAdd();
