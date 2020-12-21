<?php

$plugin = PEEP::getPluginManager()->getPlugin('privacy');

$router = PEEP::getRouter();
$router->addRoute(new PEEP_Route('privacy_index', 'member/setting/privacy', 'PRIVACY_CTRL_Privacy', 'index'));
$router->addRoute(new PEEP_Route('privacy_no_permission', 'privacy/:username/privacy-enabled', 'PRIVACY_CTRL_Privacy', 'noPermission'));

$handler = new PRIVACY_CLASS_EventHandler();

$handler->genericInit();
