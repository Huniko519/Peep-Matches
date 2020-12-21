<?php


PEEP::getRouter()->addRoute(
    new PEEP_Route('pvisitors.admin', '/admin/plugins/pvisitors', 'PVISITORS_CTRL_Admin', 'index')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('pvisitors.list', '/visitors/list', 'PVISITORS_CTRL_List', 'index')
);

PVISITORS_CLASS_EventHandler::getInstance()->init();