<?php

PEEP::getRouter()->addRoute(new PEEP_Route('contactus.index', 'contact', "CONTACTUS_CTRL_Contact", 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('contactus.admin', 'admin/plugins/contactus', "CONTACTUS_CTRL_Admin", 'dept'));

function contactus_handler_after_install( BASE_CLASS_EventCollector $event )
{
    if ( count(CONTACTUS_BOL_Service::getInstance()->getDepartmentList()) < 1 )
    {
        $url = PEEP::getRouter()->urlForRoute('contactus.admin');
        $event->add(PEEP::getLanguage()->text('contactus', 'after_install_notification', array('url' => $url)));
    }
}

PEEP::getEventManager()->bind('admin.add_admin_notification', 'contactus_handler_after_install');


function contactus_ads_enabled( BASE_CLASS_EventCollector $event )
{
    $event->add('contactus');
}

PEEP::getEventManager()->bind('ads.enabled_plugins', 'contactus_ads_enabled');

PEEP::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'CONTACTUS_CTRL_Contact');