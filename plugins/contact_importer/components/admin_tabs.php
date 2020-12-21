<?php

class CONTACTIMPORTER_CMP_AdminTabs extends BASE_CMP_ContentMenu
{
    public function __construct()
    {
        parent::__construct();

        $template = PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'content_menu.html';
        $this->setTemplate($template);

        $event = new BASE_CLASS_EventCollector(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS);
        PEEP::getEventManager()->trigger($event);
        $providers = $event->getData();

        for ( $i=0; $i < count($providers); $i++ )
        {
            $p = $providers[$i];

            if ( empty($p['settigsUrl']) )
            {
                continue;
            }

            $item = new BASE_MenuItem();
            $item->setLabel($p['title']);
            $item->setUrl($p['settigsUrl']);
            $item->setKey($p['key']);
            $item->setIconClass($p['iconClass']);
            $item->setOrder($i);

            $this->addElement($item);
        }
    }
}