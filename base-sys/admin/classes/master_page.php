<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CLASS_MasterPage extends PEEP_MasterPage
{
    private $menuCmps = array();

    /**
     * @see PEEP_MasterPage::init()
     */
    protected function init()
    {
        $language = PEEP::getLanguage();

        PEEP::getThemeManager()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(BOL_ThemeService::DEFAULT_THEME));

        $menuTypes = array(
            BOL_NavigationService::MENU_TYPE_ADMIN, BOL_NavigationService::MENU_TYPE_APPEARANCE, BOL_NavigationService::MENU_TYPE_PRIVACY,
            BOL_NavigationService::MENU_TYPE_PAGES, BOL_NavigationService::MENU_TYPE_PLUGINS, BOL_NavigationService::MENU_TYPE_SETTINGS,
            BOL_NavigationService::MENU_TYPE_USERS
        );

        $menuItems = BOL_NavigationService::getInstance()->findMenuItemsForMenuList($menuTypes);

        if ( defined('PEEP_PLUGIN_XP') )
        {
            foreach ( $menuItems as $key1 => $menuType )
            {
                foreach ( $menuType as $key2 => $menuItem )
                {
                    if ( in_array($menuItem['key'], array('sidebar_menu_plugins_add', 'sidebar_menu_themes_add')) )
                    {
                        unset($menuItems[$key1][$key2]);
                    }
                }
            }
        }

        $menuDataArray = array(
            'menu_admin' => BOL_NavigationService::MENU_TYPE_ADMIN,
            'menu_users' => BOL_NavigationService::MENU_TYPE_USERS,
            'menu_settings' => BOL_NavigationService::MENU_TYPE_SETTINGS,
            'menu_privacy' => BOL_NavigationService::MENU_TYPE_PRIVACY,
            'menu_appearance' => BOL_NavigationService::MENU_TYPE_APPEARANCE,
            'menu_pages' => BOL_NavigationService::MENU_TYPE_PAGES,
            'menu_plugins' => BOL_NavigationService::MENU_TYPE_PLUGINS
 
        );

        foreach ( $menuDataArray as $key => $value )
        {
            $this->menuCmps[$key] = new ADMIN_CMP_AdminMenu($menuItems[$value]);
            $this->addMenu($value, $this->menuCmps[$key]);
        }

        // admin notifications
        $adminNotifications = array();

        if ( !defined('PEEP_PLUGIN_XP') && PEEP::getConfig()->getValue('base', 'update_soft') )
        {
            $adminNotifications[] = $language->text('admin', 'notification_soft_update', array('link' => PEEP::getRouter()->urlForRoute('admin_core_update_request')));
        }

        $pluginsCount = BOL_PluginService::getInstance()->getPluginsToUpdateCount();

        if ( !defined('PEEP_PLUGIN_XP') && $pluginsCount > 0 )
        {
            $adminNotifications[] = $language->text('admin', 'notification_plugins_to_update', array('link' => PEEP::getRouter()->urlForRoute('admin_plugins_installed'), 'count' => $pluginsCount));
        }

        $themesCount = BOL_ThemeService::getInstance()->getThemesToUpdateCount();

        if ( !defined('PEEP_PLUGIN_XP') && $themesCount > 0 )
        {
            $adminNotifications[] = $language->text('admin', 'notification_themes_to_update', array('link' => PEEP::getRouter()->urlForRoute('admin_themes_choose'), 'count' => $themesCount));
        }

        $event = new BASE_CLASS_EventCollector('admin.add_admin_notification');
        PEEP::getEventManager()->trigger($event);

        $adminNotifications = array_merge($adminNotifications, $event->getData());

        $this->assign('notifications', $adminNotifications);

        $adminWarnings = array();

        
        if ( !defined('PEEP_PLUGIN_XP') && !ini_get('allow_url_fopen') )
        {
            $adminWarnings[] = $language->text('admin', 'warning_url_fopen_disabled');
        }

        $event = new BASE_CLASS_EventCollector('admin.add_admin_warning');
        PEEP::getEventManager()->trigger($event);

        $adminWarnings = array_merge($adminWarnings, $event->getData());
        $this->assign('warnings', $adminWarnings);

        // platform info        
        $event = new PEEP_Event('admin.get_soft_version_text');
        PEEP_EventManager::getInstance()->trigger($event);
        
        $verString = $event->getData();
        
        if ( empty($verString) )
        {
            $verString = PEEP::getLanguage()->text('admin', 'soft_version', array('version' => PEEP::getConfig()->getValue('base', 'soft_version')) );
        }
        
        $this->assign('version', PEEP::getConfig()->getValue('base', 'soft_version'));
       
        $this->assign('softVersion', $verString);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $language = PEEP::getLanguage();
        PEEP::getDocument()->setBodyClass('adminboard');
        $this->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_ADMIN));
        $arrayToAssign = array();
        srand(time());

        $script = "$('.admin_menu_cont .menu_item')
        .mouseover(function(){ $('span.menu_items', $(this)).css({display:'block'});$(this).addClass('peep_hover');})
        .mouseout(function(){ $('span.menu_items', $(this)).hide();$(this).removeClass('peep_hover');});";

        /* @var $value ADMIN_CMP_AdminMenu */
        foreach ( $this->menuCmps as $key => $value )
        {
            $id = 'mi' . rand(1, 10000);

            $value->onBeforeRender();

            $arrayToAssign[$key] = array('id' => $id, 'key' => $key, 'isActive' => $value->isActive(), 'label' => $language->text('admin', 'sidebar_' . $key), 'cmp' => ( $value->getElementsCount() < 2 || $value->isActive() ) ? '' : $value->render());

            if ( $value->isActive() && $value->getElementsCount() > 1 )
            {
                $this->assign('submenu', $value->render());
            }

            $menuItem = $value->getFirstElement();

            $script .= "$('#{$id}').click(function(e){if(!$(e.target).is('#{$id} .menu_cont *')){window.location='{$menuItem->getUrl()}';}});";
        }

        $this->assign('menuArr', $arrayToAssign);
        PEEP::getDocument()->addOnloadScript($script);
    }

    public function deleteMenu( $name )
    {
        if ( isset($this->menus[$name]) )
        {
            unset($this->menus[$name]);
        }

        if ( isset($this->menuCmps[$name]) )
        {
            unset($this->menuCmps[$name]);
        }
    }
}