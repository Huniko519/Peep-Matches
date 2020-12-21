<?php

class BASE_CMP_ConnectButtonList extends PEEP_Component
{
    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';

    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $event = new BASE_CLASS_EventCollector(self::HOOK_REMOTE_AUTH_BUTTON_LIST);
        PEEP::getEventManager()->trigger($event);
        $buttonList = $event->getData();

        if ( PEEP::getUser()->isAuthenticated() || empty($buttonList) )
        {
            $this->setVisible(false);

            return;
        }

        $markup = '';

        foreach ( $buttonList as $button )
        {
            $markup .= $button['markup'];
        }

        $this->assign('buttonList', $markup);
    }
}