<?php


BOL_BillingService::getInstance()->activateProduct('user_credits_pack');

$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('USERCREDITS_CMP_MyCreditsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 1);