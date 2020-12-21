<?php


PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_admin', 'admin/plugins/googlelocation', 'GOOGLELOCATION_CTRL_Admin', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_user_map', 'members/on-map', 'GOOGLELOCATION_CTRL_UserMap', 'map'));
PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_event_map', 'event/map', 'GOOGLELOCATION_CTRL_EventMap', 'map'));
PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_user_list', 'members/on-map/:lat/:lng/user-list/:hash', 'GOOGLELOCATION_CTRL_UserList', 'index' ));
PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_event_list', 'event/map/:lat/:lng/event-list/:hash', 'GOOGLELOCATION_CTRL_EventList', 'index' ));
PEEP::getRouter()->addRoute(new PEEP_Route('googlelocation_users_map', 'users_map', 'GOOGLELOCATION_CTRL_UserMap', 'map'));

$handler = new GOOGLELOCATION_CLASS_EventHandler();
$handler->genericInit();
$handler->init();