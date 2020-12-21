<?php

class USERCREDITS_CMP_CostOfActions extends PEEP_Component
{
    public function __construct( )
    {
        parent::__construct();

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $accountTypeId = $creditService->getUserAccountTypeId(PEEP::getUser()->getId());
        $earning = $creditService->findCreditsActions('earn', $accountTypeId, false);
        $losing = $creditService->findCreditsActions('lose', $accountTypeId, false);
        
        $this->assign('losing', $losing);
        $this->assign('earning', $earning);
    }
}
