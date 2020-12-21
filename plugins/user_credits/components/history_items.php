<?php

class USERCREDITS_CMP_HistoryItems extends PEEP_Component
{
    public function __construct( $page, $limit )
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }

        $userId = PEEP::getUser()->getId();
        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $history = $creditService->getUserLogHistory($userId, $page, $limit);
        $this->assign('history', $history);
    }
}