<?php

$router = PEEP::getRouter();

$router->addRoute(new PEEP_Route('ads.admin_settings_index', 'admin/advertisement', 'ADS_CTRL_Admin', 'index'));
$router->addRoute(new PEEP_Route('ads.banner_edit', 'admin/advertisement/:bannerId/edit', 'ADS_CTRL_Admin', 'edit'));
$router->addRoute(new PEEP_Route('ads.banner_delete', 'admin/advertisement/:bannerId/delete', 'ADS_CTRL_Admin', 'delete'));
$router->addRoute(new PEEP_Route('ads.admin_index', 'admin/advertisement/index', 'ADS_CTRL_Admin', 'index'));
$router->addRoute(new PEEP_Route('ads.admin_manage', 'admin/advertisement/manage', 'ADS_CTRL_Admin', 'manage'));

ADS_CLASS_EventHandler::getInstance()->init();