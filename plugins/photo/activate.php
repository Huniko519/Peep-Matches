<?php

PEEP::getNavigation()->addMenuItem(PEEP_Navigation::MAIN, 'view_photo_list', 'photo', 'page_title_browse_photos', PEEP_Navigation::VISIBLE_FOR_ALL);


$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
}
catch ( Exception $e )
{
    PEEP::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);
}
catch ( Exception $e )
{
    PEEP::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_UserPhotoAlbumsWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_PROFILE);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT);
}
catch ( Exception $e )
{
    PEEP::getLogger()->addEntry(json_encode($e));
}

require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new PHOTO_CLASS_Credits();
$credits->triggerCreditActionsAdd();