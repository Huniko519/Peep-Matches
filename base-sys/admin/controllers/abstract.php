<?php
/* Peepmatches Light By Peepdev co */

abstract class ADMIN_CTRL_Abstract extends PEEP_ActionController
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( PEEP::getApplication()->getContext() != PEEP_Application::CONTEXT_DESKTOP )
        {
            throw new InterceptException(array(PEEP_RequestHandler::ATTRS_KEY_CTRL => 'BASE_MCTRL_BaseDocument', PEEP_RequestHandler::ATTRS_KEY_ACTION => 'notAvailable'));
        }

        if ( !PEEP::getUser()->isAdmin() )
        {
            throw new AuthenticateException();
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            $document = PEEP::getDocument();
            $document->setMasterPage(new ADMIN_CLASS_MasterPage());
            $this->setPageTitle(PEEP::getLanguage()->text('admin', 'page_default_title'));
        }

        BOL_PluginService::getInstance()->checkManualUpdates();
        BOL_ThemeService::getInstance()->checkManualUpdates();
        $plugin = BOL_PluginService::getInstance()->findNextManualUpdatePlugin();

        $handlerParams = PEEP::getRequestHandler()->getHandlerAttributes();

        // TODO refactor shortcut below
        if ( !defined('PEEP_PLUGIN_XP') && $plugin !== null )
        {
            if ( ( $handlerParams['controller'] === 'ADMIN_CTRL_Plugins' && $handlerParams['action'] === 'manualUpdateRequest' ) )
            {
                //action
            }
            else
            {
                throw new RedirectException(PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'manualUpdateRequest', array('key' => $plugin->getKey())));
            }
        }

        // TODO temp admin pge inform event
        function admin_check_if_admin_page()
        {
            return true;
        }
        PEEP::getEventManager()->bind('admin.check_if_admin_page', 'admin_check_if_admin_page');
    }

    public function setPageTitle( $title )
    {
        PEEP::getDocument()->setTitle($title);
    }
}
