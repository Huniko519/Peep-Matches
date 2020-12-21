<?php

class NOTIFICATIONS_CMP_NotificationItem extends BASE_CMP_ConsoleListIpcItem
{
    public function __construct()
    {
        parent::__construct();

        $plugin = PEEP::getPluginManager()->getPlugin('BASE');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_list_ipc_item.html');

        $this->addClass('peep_notification_item');
    }
}