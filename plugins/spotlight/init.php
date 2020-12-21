<?php


$plugin = PEEP::getPluginManager()->getPlugin('spotlight');

$key = strtoupper($plugin->getKey());

PEEP::getRouter()->addRoute(new PEEP_Route('spotlight-admin-settings', 'admin/spotlight/settings', "{$key}_CTRL_Admin", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('spotlight-add-to-list', 'spotlight/ajax', "{$key}_CTRL_Index", 'ajax'));

$credits = new SPOTLIGHT_CLASS_Credits();
PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));


function spotlight_usercredits_active( BASE_CLASS_EventCollector $event )
{
    if ( !PEEP::getPluginManager()->isPluginActive('usercredits') )
    {
        $language = PEEP::getLanguage();

        $event->add($language->text('spotlight', 'error_usercredits_not_installed'));
    }
}
PEEP::getEventManager()->bind('admin.add_admin_notification', 'spotlight_usercredits_active');

$hlEventHandler = new SPOTLIGHT_CLASS_EventHandler();
$hlEventHandler->init();
