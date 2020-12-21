<?php

$plugin = PEEP::getPluginManager()->getPlugin('notifications');

PEEP::getRouter()->addRoute(new PEEP_Route('notifications-settings', 'member/setting/notifications', 'NOTIFICATIONS_CTRL_Notifications', 'settings'));
PEEP::getRouter()->addRoute(new PEEP_Route('notifications-unsubscribe', 'email-notifications/unsubscribe/:code/:action', 'NOTIFICATIONS_CTRL_Notifications', 'unsubscribe'));

NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->init();
NOTIFICATIONS_CLASS_EmailBridge::getInstance()->init();

function notifications_preference_menu_item( BASE_CLASS_EventCollector $event )
{
    $router = PEEP_Router::getInstance();
    $language = PEEP::getLanguage();

    $menuItems = array();

    $menuItem = new BASE_MenuItem();

    $menuItem->setKey('email_notifications');
    $menuItem->setLabel($language->text( 'notifications', 'dashboard_menu_item'));
    $menuItem->setIconClass('peep_ic_mail');
    $menuItem->setUrl($router->urlForRoute('notifications-settings'));
    $menuItem->setOrder(3);

    $event->add($menuItem);
}

PEEP::getEventManager()->bind('base.preference_menu_items', 'notifications_preference_menu_item');

    



