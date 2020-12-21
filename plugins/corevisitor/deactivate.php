<?php
$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    $widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e )
{
    PEEP::getLogger()->addEntry(json_encode($e));
}

try
{
    $widget = $widgetService->addWidget('BASE_CMP_UserListWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
}
catch ( Exception $e )
{
    PEEP::getLogger()->addEntry(json_encode($e));
}

$widgetService->deleteWidget('COREVISITOR_CMP_UserCarouselWidget');
