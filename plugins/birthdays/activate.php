<?php

//add 'birthdays' widget to index page
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_BirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_INDEX
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_FriendBirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_DASHBOARD
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT,1);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_MyBirthdayWidget', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_CelebrationWidget', false),
        BOL_ComponentAdminService::PLACE_DASHBOARD
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT,-1);

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new BIRTHDAYS_CLASS_Credits();
$credits->triggerCreditActionsAdd();
