<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Finance extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Finance list page controller
     *
     * @param array $params
     */
    public function index( array $params )
    {
        $service = BOL_BillingService::getInstance();
        $lang = PEEP::getLanguage();

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $onPage = 20;
        $list = $service->getFinanceList($page, $onPage);

        $userIdList = array();
        foreach ( $list as $sale )
        {
            if ( isset($sale['userId']) && !in_array($sale['userId'], $userIdList))
            {
                array_push($userIdList, $sale['userId']);
            }
        }
        
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($userIdList);

        $this->assign('list', $list);
        $this->assign('displayNames', $displayNames);
        $this->assign('userNames', $userNames);
        
        $total = $service->countSales();
        
        // Paging
        $pages = (int) ceil($total / $onPage);
        $paging = new BASE_CMP_Paging($page, $pages, 10);
        $this->assign('paging', $paging->render());
    
        $this->assign('total', $total);
        
        $stats = $service->getTotalIncome();
        $this->assign('stats', $stats);
        
        PEEP::getDocument()->setHeading($lang->text('admin', 'page_title_finance'));
        PEEP::getDocument()->setHeadingIconClass('peep_ic_app');
    }
}