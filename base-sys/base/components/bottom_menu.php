<?php

class BASE_CMP_BottomMenu extends BASE_CMP_Menu
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $menuItems = BOL_NavigationService::getInstance()->findMenuItems(BOL_NavigationService::MENU_TYPE_BOTTOM);
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($menuItems));
        $this->name = BOL_NavigationService::MENU_TYPE_BOTTOM;
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir().'bottom_menu.html');

    }
}