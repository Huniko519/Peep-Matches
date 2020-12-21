<?php

class BASE_CMP_DashboardContentMenu extends BASE_CMP_ContentMenu
{

    public function __construct()
    {
        $event = new BASE_CLASS_EventCollector('base.dashboard_menu_items');

        PEEP::getEventManager()->trigger($event);

        $menuItems = $event->getData();

        parent::__construct($menuItems);
    }
}
