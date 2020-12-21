<?php

class BASE_CMP_ContentMenu extends BASE_CMP_Menu
{
    public function __construct( $menuItems = null )
    {
        parent::__construct($menuItems);
        
        $this->setTemplate(PEEP::getPluginManager()
                ->getPlugin('base')->getCmpViewDir().'content_menu.html');
    }
}