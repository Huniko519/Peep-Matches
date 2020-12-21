<?php


BOL_ComponentAdminService::getInstance()->deleteWidget('CNEWS_CMP_MyFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('CNEWS_CMP_EntityFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('CNEWS_CMP_SiteFeedWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('CNEWS_CMP_UserFeedWidget');

// Mobile deactivation
PEEP::getNavigation()->deleteMenuItem('cnews', 'cnews_feed');
