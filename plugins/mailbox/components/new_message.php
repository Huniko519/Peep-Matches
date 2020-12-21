<?php

class MAILBOX_CMP_NewMessage extends PEEP_Component
{

    public function __construct()
    {
        parent::__construct();

        $form = PEEP::getClassInstance("MAILBOX_CLASS_NewMessageForm", $this);
        /* @var $user MAILBOX_CLASS_NewMessageForm */
        
        $this->addForm($form);

        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $this->assign('displayCaptcha', false);

        $configs = PEEP::getConfig()->getValues('mailbox');
        $this->assign('enableAttachments', !empty($configs['enable_attachments']));
    }
}