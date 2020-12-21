<?php

$widget = BOL_ComponentAdminService::getInstance()->addWidget('CONTACTIMPORTER_CMP_Widget');
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 0);


