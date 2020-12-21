<?php

class USERCREDITS_CMP_GrantCredits extends PEEP_Component
{
    public function __construct( $userId )
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }
        
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        $amount = $creditService->getGrantableAmountForUser(PEEP::getUser()->getId());

        $form = new USERCREDITS_CLASS_GrantCreditsForm();
        $form->getElement('userId')->setValue($userId);
        $form->getElement('amount')->setValue($amount);

        $this->addForm($form);
    }
}
