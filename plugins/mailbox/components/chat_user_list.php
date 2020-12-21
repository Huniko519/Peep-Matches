<?php

class MAILBOX_CMP_ChatUserList extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
  
    }

    public function render()
    {
        $userId = PEEP::getUser()->getId();

        $userSettingsForm = MAILBOX_BOL_ConversationService::getInstance()->getUserSettingsForm();
        $this->addForm($userSettingsForm);
        $userSettingsForm->getElement('user_id')->setValue($userId);

        $friendsEnabled = (bool)PEEP::getEventManager()->call('plugin.friends');
        $this->assign('friendsEnabled', $friendsEnabled);

        $showAllMembersModeEnabled = (bool)PEEP::getConfig()->getValue('mailbox', 'show_all_members');
        $this->assign('showAllMembersModeEnabled', $showAllMembersModeEnabled);

        return parent::render();
    }

}
