<?php

PEEP::getRouter()->addRoute( 
    new PEEP_Route('socialsharing.admin', 'admin/plugins/social-sharing', 'SOCIALSHARING_CTRL_Admin', 'index')
);



 
SOCIALSHARING_CLASS_EventHandler::getInstance()->genericInit();

function socialsharing_add_admin_notification( BASE_CLASS_EventCollector $coll )
{
    $config = PEEP::getConfig();

    if ( $config->getValue('socialsharing', 'api_key') )
    {
        return;
    }

    $coll->add(
            PEEP::getLanguage()->text( 'socialsharing', 'plugin_installation_notice', array('url' => PEEP::getRouter()->urlForRoute('socialsharing.admin') ) )
        );
}
PEEP::getEventManager()->bind('admin.add_admin_notification', 'socialsharing_add_admin_notification');

