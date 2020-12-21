<?php

final class PEEP_Navigation
{
    const MAIN = BOL_NavigationService::MENU_TYPE_MAIN;
    const BOTTOM = BOL_NavigationService::MENU_TYPE_BOTTOM;
    
    
    const ADMIN_PLUGINS = BOL_NavigationService::MENU_TYPE_PLUGINS;
    const ADMIN_USERS = BOL_NavigationService::MENU_TYPE_USERS;
    const ADMIN_APPEARANCE = BOL_NavigationService::MENU_TYPE_APPEARANCE;
    const ADMIN_SETTINGS = BOL_NavigationService::MENU_TYPE_SETTINGS;
    const ADMIN_PAGES = BOL_NavigationService::MENU_TYPE_PAGES;
    const ADMIN_DEV_TOOLS = BOL_NavigationService::MENU_TYPE_PRIVACY;
    const ADMIN_DASHBOARD = BOL_NavigationService::MENU_TYPE_ADMIN;
    

    const VISIBLE_FOR_GUEST = BOL_NavigationService::VISIBLE_FOR_GUEST;
    const VISIBLE_FOR_MEMBER = BOL_NavigationService::VISIBLE_FOR_MEMBER;
    const VISIBLE_FOR_ALL = BOL_NavigationService::VISIBLE_FOR_ALL;

    /**
     * @var BOL_NavigationService
     */
    private $navService;
    /**
     * Singleton instance.
     * 
     * @var PEEP_Navigation
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     * 
     * @return PEEP_Navigation
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->navService = BOL_NavigationService::getInstance();
    }

    /**
     * Adds menu items to global menu system.
     *
     * @param string $menuType
     * @param string $routeName
     * @param string $prefix
     * @param string $key
     * @param string $visibleFor
     */
    public function addMenuItem( $menuType, $routeName, $prefix, $key, $visibleFor = self::VISIBLE_FOR_ALL )
    {
        if ( empty($menuType) || empty($routeName) || empty($prefix) || empty($key) )
        {
            throw new InvalidArgumentException();
        }

        $menuType = trim($menuType);

        $order = $this->navService->findMaxSortOrderForMenuType($menuType);

        $menuItem = new BOL_MenuItem();
        $menuItem->setType($menuType);
        $menuItem->setRoutePath($routeName);
        $menuItem->setPrefix($prefix);
        $menuItem->setKey($key);
        $menuItem->setOrder(($order + 1));
        $menuItem->setVisibleFor($visibleFor);

        $this->navService->saveMenuItem($menuItem);
    }

    /**
     * Deletes menu item.
     *
     * @param string $prefix
     * @param string $key
     */
    public function deleteMenuItem( $prefix, $key )
    {
        $menuItem = $this->navService->findMenuItem($prefix, $key);

        if ( $menuItem !== null )
        {
            $this->navService->deleteMenuItem($menuItem);
        }
    }

    /**
     * Activates system menu items. 
     * 
     * @param string $menuType
     * @param string $prefix
     * @param string $key
     */
    public function activateMenuItem( $menuType, $prefix, $key )
    {
        if ( PEEP::getDocument()->getMasterPage() === null )
        {
            return;
        }

        $menu = PEEP::getDocument()->getMasterPage()->getMenu(trim($menuType));

        if ( $menu === null )
        {
            //trigger_error("Can't find menu in master page -  `" . $menuType . "`!", E_USER_WARNING);
            return;
        }

        $menuItem = $menu->getElement($key, $prefix);

        if ( $menuItem === null )
        {
            //trigger_error("Can't find menu item `" . $key . "` in menu `" . $menuType . "`!", E_USER_WARNING);
            return;
        }

        $menuItem->setActive(true);
    }

    /**
     * Deactivates all elements of provided menu.
     * @param string $menuType
     */
    public function deactivateMenuItems( $menuType )
    {
        $menu = PEEP::getDocument()->getMasterPage()->getMenu(trim($menuType));

        if ( $menu === null )
        {
            trigger_error("Can't find menu in master page -  `" . $menuType . "`!", E_USER_WARNING);
            return;
        }

        $menu->deactivateElements();
    }
}