<?php


$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('PVISITORS_CMP_MyVisitorsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT);