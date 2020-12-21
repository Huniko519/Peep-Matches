<?php

class USERCREDITS_CTRL_Credits extends PEEP_ActionController
{
    public function history()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();

        $lang = PEEP::getLanguage();
        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $limit = 20;

        $this->addComponent('items', new USERCREDITS_CMP_HistoryItems($page, $limit));
        $records = $creditService->countUserLogEntries(PEEP::getUser()->getId());

        // Paging
        $pages = (int) ceil($records / $limit);
        $paging = new BASE_CMP_Paging($page, $pages, 10);
        $this->assign('paging', $paging->render());

        $this->setPageHeading($lang->text('usercredits', 'credits_history_page_heading'));
        PEEP::getDocument()->setTitle($lang->text('usercredits', 'credits_history_page_heading'));

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'dashboard');
    }
}