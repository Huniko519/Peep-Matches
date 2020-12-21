<?php

class USERCREDITS_CMP_Earn extends PEEP_Component
{
    public function __construct( )
    {
        parent::__construct();

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $accountTypeId = $creditService->getUserAccountTypeId(PEEP::getUser()->getId());
        $earning = $creditService->findCreditsActions('earn', $accountTypeId, false);

        $this->assign('earning', $earning);
    }
}
