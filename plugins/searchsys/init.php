<?php
PEEP::getRouter()->addRoute(
	new PEEP_Route('searchsys.admin-config', 'admin/plugins/searchsys/', 'SEARCHSYS_CTRL_Admin', 'index')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('searchsys.admin-site-search', 'admin/plugins/searchsys/site/', 'SEARCHSYS_CTRL_Admin', 'site')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('searchsys.search-result', 'site-search/', 'SEARCHSYS_CTRL_Search', 'result')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('searchsys.search-action', 'searchsys/search-action/', 'SEARCHSYS_CTRL_Search', 'ajaxSearchAction')
);

PEEP::getRouter()->addRoute(
    new PEEP_Route('searchsys.set-acc-type', 'searchsys/set-acc-type/', 'SEARCHSYS_CTRL_Search', 'ajaxSetAccType')
);

function searchsys_add_auth_labels( BASE_CLASS_EventCollector $event )
{
    $language = PEEP::getLanguage();
    $event->add(
        array(
            'searchsys' => array(
                'label' => $language->text('searchsys', 'auth_group_label'),
                'actions' => array(
                    'search_system' => $language->text('searchsys', 'auth_action_label_search'),
                    'site_search'  => $language->text('searchsys', 'auth_action_label_site_search'),
                )
            )
        )
    );
}
PEEP::getEventManager()->bind('admin.add_auth_labels', 'searchsys_add_auth_labels');

function searchsys_add_styles( PEEP_Event $e )
{
    if ( !PEEP::getConfig()->getValue('searchsys', 'site_search_enabled') )
    {
        return;
    }

    $styles = '.console_search_form .esel2-container-multi .esel2-choices .esel2-search-field input, .us-field-fake input {
        padding: 0 5px;
        margin: 0;
        height: 35px;
        line-height: 35px;
    }';
    PEEP::getDocument()->addStyleDeclaration($styles);
}
PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_DOCUMENT_RENDER, 'searchsys_add_styles');

$credits = new SEARCHSYS_CLASS_Credits();
PEEP::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionCollect'));

function searchsys_init_site_search_events()
{
    SEARCHSYS_CLASS_EventHandler::getInstance()->init();
}
PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, 'searchsys_init_site_search_events');