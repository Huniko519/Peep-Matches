<?php

PEEP::getNavigation()->deleteMenuItem('stories', 'main_menu_item');

BOL_ComponentAdminService::getInstance()->deleteWidget('STORIES_CMP_UserStoryWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('STORIES_CMP_StoryWidget');