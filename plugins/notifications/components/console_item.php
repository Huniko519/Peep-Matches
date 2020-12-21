<?php

class NOTIFICATIONS_CMP_ConsoleItem extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        $label = PEEP::getLanguage()->text('notifications', 'console_item_label');

        parent::__construct( $label, NOTIFICATIONS_CLASS_ConsoleBridge::CONSOLE_ITEM_KEY );

        $this->addClass('peep_notification_list');
    }

    public function initJs()
    {
        parent::initJs();

        $staticUrl = PEEP::getPluginManager()->getPlugin('notifications')->getStaticUrl();
        PEEP::getDocument()->addScript($staticUrl . 'notifications.js');

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.Notification = new PEEP_Notification({$key});', array(
            'key' => $this->getKey()
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }
}