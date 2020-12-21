<?php

$widget = BOL_ComponentAdminService::getInstance()->addWidget('ADS_CMP_RightAds', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget('ADS_CMP_LeftAds', false);

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 0);

$navigation = PEEP::getNavigation();