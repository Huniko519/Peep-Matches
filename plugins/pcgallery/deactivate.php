<?php

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('BASE_CMP_UserAvatarWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);

try 
{
    $widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e ) {}
