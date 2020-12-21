<?php

/* $widget = BOL_ComponentAdminService::getInstance()->addWidget('SOCIALSHARING_CMP_ProfileShareButtonsWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT); */

$widget = BOL_ComponentAdminService::getInstance()->addWidget('SOCIALSHARING_CMP_IndexShareButtonsWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT, 0);
