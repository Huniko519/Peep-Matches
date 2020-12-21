<?php

class BASE_CMP_MainMenu extends BASE_CMP_Menu
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = BOL_NavigationService::MENU_TYPE_MAIN;
        $menuItems = BOL_NavigationService::getInstance()->findMenuItems(BOL_NavigationService::MENU_TYPE_MAIN);
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($menuItems));
    }
}