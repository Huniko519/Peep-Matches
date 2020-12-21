<?php

PEEP::getRouter()->addRoute(new PEEP_Route('contactimporter_facebook_canvas', 'contactimporter/fbcanvas', 'CONTACTIMPORTER_CTRL_Facebook', 'canvas'));
PEEP::getRouter()->addRoute(new PEEP_Route('contactimporter_facebook_settings', 'admin/plugins/contactimporter/facebook', 'CONTACTIMPORTER_CTRL_Admin', 'facebook'));

PEEP::getRouter()->addRoute(new PEEP_Route('contactimporter_google_settings', 'admin/plugins/contactimporter/google', 'CONTACTIMPORTER_CTRL_Admin', 'google'));

PEEP::getRouter()->addRoute(new PEEP_Route('invite.index', 'invite', "CONTACTIMPORTER_CTRL_InvitePage", 'index')); 


PEEP::getRouter()->addRoute(new PEEP_Route('contactimporter_admin', 'admin/plugins/contactimporter', 'CONTACTIMPORTER_CTRL_Admin', 'admin'));
PEEP::getRouter()->addRoute(new PEEP_Route('contact-importer-admin', 'admin/plugins/contactimporter', 'CONTACTIMPORTER_CTRL_Admin', 'admin'));

PEEP::getRouter()->addRoute(new PEEP_Route('contact-importer-google-oauth', 'google/oauth', 'CONTACTIMPORTER_CTRL_Google', 'oauth2callback'));

$eventHandler = new CONTACTIMPORTER_CLASS_EventHandler;

PEEP::getEventManager()->bind(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS, array($eventHandler, 'collectProviders'));
PEEP::getEventManager()->bind(CONTACTIMPORTER_CLASS_EventHandler::EVENT_RENDER_BUTTON, array($eventHandler, 'buttonRender'));
PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_REGISTER, array($eventHandler, 'onUserRegister'));

PEEP::getEventManager()->bind(PEEP_EventManager::ON_JOIN_FORM_RENDER, array($eventHandler, 'onJoinFormRender'));


function contactimporter_add_admin_notification( BASE_CLASS_EventCollector $e )
{
    $language = PEEP::getLanguage();
    $configs = PEEP::getConfig()->getValues('contactimporter');

    if ( empty($configs['facebook_app_id']) || empty($configs['google_client_id']) || empty($configs['google_client_secret']) || empty($configs['facebook_app_secret']) )
    {
        $e->add($language->text('contactimporter', 'requires_configuration_message', array( 'settingsUrl' => PEEP::getRouter()->urlForRoute('contactimporter_admin') )));
    }
}
PEEP::getEventManager()->bind('admin.add_admin_notification', 'contactimporter_add_admin_notification');

function contactimporter_add_access_exception( BASE_CLASS_EventCollector $e )
{
    $e->add(array('controller' => 'CONTACTIMPORTER_CTRL_Facebook', 'action' => 'canvas'));
}

PEEP::getEventManager()->bind('base.members_only_exceptions', 'contactimporter_add_access_exception');
PEEP::getEventManager()->bind('base.password_protected_exceptions', 'contactimporter_add_access_exception');
PEEP::getEventManager()->bind('base.splash_screen_exceptions', 'contactimporter_add_access_exception');

PEEP::getApplication()->addHttpsHandlerAttrs('CONTACTIMPORTER_CTRL_Facebook', 'canvas');

function contactimporter_add_console_item( BASE_CLASS_EventCollector $event )
{
	$event->add(array('label' => PEEP::getLanguage()->text('contactimporter', 'widget_title'), 'url' => PEEP::getRouter()->urlForRoute('invite.index')));
}
PEEP::getEventManager()->bind('base.add_main_console_item', 'contactimporter_add_console_item');

$eventHandler = CONTACTIMPORTER_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();


