<?php


$plugin = PEEP::getPluginManager()->getPlugin('googleauth');

//Route che punta al login : googleauth_login
PEEP::getRouter()->addRoute(new PEEP_Route('googleauth_oauth', 'googleauth/oauth', 'GOOGLEAUTH_CTRL_Connect', 'oauth'));

//Route che punta alla prima maschera di configurazione
PEEP::getRouter()->addRoute(new PEEP_Route('googleauth_admin_main','admin/plugins/googleauth','GOOGLEAUTH_CTRL_Admin', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('googleauth_app_success_page','admin/plugins/googleauth','GOOGLEAUTH_CRTL_Admin', 'success'));

$configs = PEEP::getConfig()->getValues('googleauth');
if ( !empty($configs['client_id']) && !empty($configs['client_secret']) ) 
     {
	$registry = PEEP::getRegistry();
	$registry->addToArray(BASE_CTRL_Join::JOIN_CONNECT_HOOK, array(new GOOGLEAUTH_CMP_ConnectButton(), 'render'));
	$registry->addToArray(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, array(new GOOGLEAUTH_CMP_ConnectButton(), 'render'));
     }

function googleauth_event_add_button( BASE_CLASS_EventCollector $event )
{
    $cssUrl = PEEP::getPluginManager()->getPlugin('GOOGLEAUTH')->getStaticCssUrl() . 'googleauth.css';
    PEEP::getDocument()->addStyleSheet($cssUrl);
    $button = new GOOGLEAUTH_CMP_ConnectButton();
    $event->add(array('iconClass' => 'peep_ico_signin_g', 'markup' => $button->render()));
}
PEEP::getEventManager()->bind(BASE_CMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, 'googleauth_event_add_button');

//Funzione per notificare che si necessita la configurazione del plugin
function googleauth_add_admin_notification( BASE_CLASS_EventCollector $e )
 {
    $language = PEEP::getLanguage();
    $configs = PEEP::getConfig()->getValues('googleauth');
    if ( empty($configs['client_id']) || empty($configs['client_secret']) )
    {
        $e->add($language->text('googleauth', 'admin_configuration_required_notification', array( 'href' => PEEP::getRouter()->urlForRoute('googleauth_admin_main') )));
    }
 }
PEEP::getEventManager()->bind('admin.add_admin_notification', 'googleauth_add_admin_notification');

function googleauth_add_access_exception( BASE_CLASS_EventCollector $e ) {
	$e->add(array('controller' => 'GOOGLEAUTH_CTRL_Connect', 'action' => 'oauth'));

}

PEEP::getEventManager()->bind('base.members_only_exceptions', 'googleauth_add_access_exception');
PEEP::getEventManager()->bind('base.password_protected_exceptions', 'googleauth_add_access_exception');
PEEP::getEventManager()->bind('base.splash_screen_exceptions', 'googleauth_add_access_exception');

$eventHandler = new GOOGLEAUTH_CLASS_EventHandler();
$eventHandler->init();
