<?php

class BASE_CMP_ConsoleInvitations extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        $label = PEEP::getLanguage()->text('base', 'console_item_invitations_label');

        parent::__construct( $label, 'invitation' );

        $this->addClass('peep_invitation_list');
    }

    public function initJs()
    {
        parent::initJs();

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('PEEP.Invitation = new PEEP_Invitation({$key}, {$params});', array(
            'key' => $this->getKey(),
            'params' => array(
                'rsp' => PEEP::getRouter()->urlFor('BASE_CTRL_Invitation', 'ajax')
            )
        ));
        
        PEEP::getDocument()->addOnloadScript($js);
    }
}