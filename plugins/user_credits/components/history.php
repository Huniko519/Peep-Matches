<?php

class USERCREDITS_CMP_History extends PEEP_Component
{
    const HISTORY_DISPLAY_ENTRY_LIMIT = 8;

    public function __construct()
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
        }

        $lang = PEEP::getLanguage();
        $this->addComponent('items', new USERCREDITS_CMP_HistoryItems(1, self::HISTORY_DISPLAY_ENTRY_LIMIT));

        $userId = PEEP::getUser()->getId();
        $loadMore = USERCREDITS_BOL_CreditsService::getInstance()->countUserLogEntries($userId) > self::HISTORY_DISPLAY_ENTRY_LIMIT;
        $this->assign('loadMore', $loadMore);

        $toolbar = array();
        if ( $loadMore )
        {
            $toolbar = array(array(
                'label' => $lang->text('usercredits', 'view_more'),
                'href' => PEEP::getRouter()->urlForRoute('usercredits.history')
            ));
        }

        $this->assign('toolbar', $toolbar);

    }
}