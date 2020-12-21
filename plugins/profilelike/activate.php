<?php


$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('PROFILELIKE_CMP_Profilelikebutton', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$profileWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('PROFILELIKE_CMP_LikedWidget', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($profileWidget, BOL_ComponentAdminService::SECTION_LEFT);

$profileWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('PROFILELIKE_CMP_LikesWidget', false),
        BOL_ComponentAdminService::PLACE_DASHBOARD
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($profileWidget, BOL_ComponentAdminService::SECTION_RIGHT,3);
