<?php

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('CNEWS_CMP_MyFeedWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_LEFT, 0);

$event = new BASE_CLASS_EventCollector('feed.collect_widgets');
PEEP::getEventManager()->trigger($event);

foreach( $event->getData() as $widgetInfo )
{
    try
    {
        $widget = $widgetService->addWidget('CNEWS_CMP_EntityFeedWidget', false);
        $widgetPlace = $widgetService->addWidgetToPlace($widget, $widgetInfo['place']);
        $widgetService->addWidgetToPosition($widgetPlace, $widgetInfo['section'], $widgetInfo['order']);
    }
    catch ( Exception $e )
    {

    }
}


require_once dirname(__FILE__) . DS .  'classes' . DS . 'credits.php';
$credits = new CNEWS_CLASS_Credits();
$credits->triggerCreditActionsAdd();

