<?php

PEEP::getRouter()->addRoute(new PEEP_Route('friends_list', 'friends', 'FRIENDS_CTRL_List', 'index', array('list' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'friends'))));
PEEP::getRouter()->addRoute(new PEEP_Route('friends_lists', 'friends/:list', 'FRIENDS_CTRL_List', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('friends_user_friends', 'friends/user/:user', 'FRIENDS_CTRL_List', 'index', array('list' => array(PEEP_Route::PARAM_OPTION_HIDDEN_VAR => 'user-friends'))));


if ( PEEP::getPluginManager()->getPlugin('friends')->getDto()->build >= 5836 )
{
    FRIENDS_CLASS_RequestEventHandler::getInstance()->init();
}

$eventHandler = FRIENDS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

PEEP::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME,  array($eventHandler,'onCollectProfileActionTools'));
PEEP::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME,      array($eventHandler,'onCollectQuickLinks'));
