<?php

PEEP::getRouter()->addRoute(new PEEP_Route('view_photo_list', 'photo/viewlist/:listType/', 'PHOTO_CTRL_Photo', 'viewList', array('listType' => array('default' => 'latest'))));
PEEP::getRouter()->addRoute(new PEEP_Route('view_tagged_photo_list_st', 'photo/viewlist/tagged/', 'PHOTO_CTRL_Photo', 'viewTaggedList'));
PEEP::getRouter()->addRoute(new PEEP_Route('view_tagged_photo_list', 'photo/viewlist/tagged/:tag', 'PHOTO_CTRL_Photo', 'viewTaggedList'));
PEEP::getRouter()->addRoute(new PEEP_Route('view_photo', 'photo/view/:id', 'PHOTO_CTRL_Photo', 'view'));
PEEP::getRouter()->addRoute(new PEEP_Route('view_photo_type', 'photo/view/:id/:listType', 'PHOTO_CTRL_Photo', 'view', array('listType' => array('default' => 'latest'))));
PEEP::getRouter()->addRoute(new PEEP_Route('photo_admin_config', 'photo/admin', 'PHOTO_CTRL_Admin', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo_admin_view', 'photo/admin/view', 'PHOTO_CTRL_Admin', 'view'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo_uninstall', 'photo/admin/uninstall', 'PHOTO_CTRL_Admin', 'uninstall'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo_user_albums', 'photo/useralbums/:user/', 'PHOTO_CTRL_Photo', 'userAlbums'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo_user_album', 'photo/useralbum/:user/:album', 'PHOTO_CTRL_Photo', 'userAlbum'));

PEEP::getRouter()->addRoute(new PEEP_Route('photo.user_photos', 'photo/userphotos/:user/', 'PHOTO_CTRL_Photo', 'userPhotos'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_upload', 'photo/ajax-upload', 'PHOTO_CTRL_AjaxUpload', 'upload'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_upload_submit', 'photo/ajax-upload-submit', 'PHOTO_CTRL_AjaxUpload', 'ajaxSubmitPhotos'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_upload_delete', 'photo/ajax-upload-delete', 'PHOTO_CTRL_AjaxUpload', 'delete'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_create_photo', 'photo/ajax-create-album', 'PHOTO_CTRL_Photo', 'ajaxCreateAlbum'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_update_photo', 'photo/ajax-update-album', 'PHOTO_CTRL_Photo', 'ajaxUpdateAlbum'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.ajax_album_cover', 'photo/ajax-album-cover', 'PHOTO_CTRL_Photo', 'ajaxCropPhoto'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.download_photo', 'photo/download-photo/:id', 'PHOTO_CTRL_Photo', 'downloadPhoto'));
PEEP::getRouter()->addRoute(new PEEP_Route('photo.approve', 'photo/approve/:id', 'PHOTO_CTRL_Photo', 'approve'));

PHOTO_CLASS_EventHandler::getInstance()->init();
