<?php

PEEP::getNavigation()->deleteMenuItem('photo', 'page_title_browse_photos');

BOL_ComponentAdminService::getInstance()->deleteWidget('PHOTO_CMP_PhotoListWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('PHOTO_CMP_UserPhotoAlbumsWidget');