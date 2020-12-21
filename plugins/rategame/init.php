<?php

$plugin = PEEP::getPluginManager()->getPlugin('rategame');

PEEP::getRouter()->addRoute(new PEEP_Route('rate_photo_game', 'rategame/rate/:sex', 'RATEGAME_CTRL_Rate', 'index', array('sex'=>array(PEEP_Route::PARAM_OPTION_DEFAULT_VALUE => 0)) ));
PEEP::getRouter()->addRoute(new PEEP_Route('rate_next_photo', 'rategame/rate/get-next-photo', 'RATEGAME_CTRL_Rate', 'getNextPhoto'));
PEEP::getRouter()->addRoute(new PEEP_Route('refresh_photo', 'rategame/rate/refresh-photo', 'RATEGAME_CTRL_Rate', 'refreshPhoto'));

function rategame_is_photo_active( BASE_CLASS_EventCollector $event )
{
    if ( !PEEP::getPluginManager()->isPluginActive('photo') )
    {
        $language = PEEP::getLanguage();

        $event->add($language->text('rategame', 'error_photo_not_installed'));
    }
}
PEEP::getEventManager()->bind('admin.add_admin_notification', 'rategame_is_photo_active');