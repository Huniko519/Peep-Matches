<?php

$widgetService = BOL_ComponentAdminService::getInstance();

/*
$widget = $widgetService->addWidget('COREVISITOR_CMP_UserCarouselWidget', false);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
 */

$widgetService->deleteWidget('PHOTO_CMP_PhotoListWidget');
$widgetService->deleteWidget('BASE_CMP_UserListWidget');

$widget = $widgetService->addWidget('PHOTO_CMP_PhotoListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);

$widget = $widgetService->addWidget('BASE_CMP_UserListWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);