<?php

class USERCREDITS_CMP_SetCredits extends PEEP_Component
{
    public function __construct( $userId )
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthorized('usercredits') )
        {
            $this->setVisible(false);
        }
        
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        $balance = $creditService->getCreditsBalance($userId);

        $form = new USERCREDITS_CLASS_SetCreditsForm();
        $form->getElement('userId')->setValue($userId);
        $form->getElement('balance')->setValue($balance);

        $this->addForm($form);

        $this->assign('balance', $balance);
    }
}
