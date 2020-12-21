<?php

class BASE_CTRL_Billing extends PEEP_ActionController
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Controller action for completed orders

     * @param array $params
     */
    public function completed( array $params )
    {
        $billingService = BOL_BillingService::getInstance();
        $lang = PEEP::getLanguage();

        if ( isset($params['hash']) )
        {
            if ( !$sale = $billingService->getSaleByHash($params['hash']) )
            {
                $msg = $lang->text('base', 'billing_sale_not_found');
            }
            else 
            {
                switch ( $sale->status )
                {
                    case BOL_BillingSaleDao::STATUS_DELIVERED:
                        $msg = $lang->text('base', 'billing_order_completed_successfully');
                        break;
                    
                    case BOL_BillingSaleDao::STATUS_VERIFIED:
                        $msg = $lang->text('base', 'billing_order_verified');
                        break;
                        
                    case BOL_BillingSaleDao::STATUS_PREPARED:
                    case BOL_BillingSaleDao::STATUS_PROCESSING:
                        $msg = $lang->text('base', 'billing_order_processing');
                        break;
                        
                    case BOL_BillingSaleDao::STATUS_ERROR:
                        $msg = $lang->text('base', 'billing_order_failed');
                        break;
    
                    default:
                        $msg = $lang->text('base', 'billing_order_failed');
                        break; 
                }
            }
        }
        else 
        {
            $msg = $lang->text('base', 'billing_order_completed_successfully');
        }
        
        $this->assign('message', $msg);
        
        $this->setPageHeading($lang->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_cart');
    }

    /**
     * Controller action for canceled orders
     * 
     * @param $params
     */
    public function canceled( array $params )
    {
        $this->assign('message', PEEP::getLanguage()->text('base', 'billing_order_canceled'));
        
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_cart');
    }
    
    /**
     * Controller action for failed orders
     * 
     * @param $params
     */
    public function error( array $params )
    {
        $this->assign('message', PEEP::getLanguage()->text('base', 'billing_order_failed'));
        
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_cart');
    }
    
    public function saveGatewayProduct()
    {
        if ( PEEP::getRequest()->isPost() && $_POST['action'] == 'update_products' )
        {
            $service = BOL_BillingService::getInstance();
            
            foreach ( $_POST['products'] as $id => $prodId ) 
            {
                $service->updateGatewayProduct($id, $prodId);
            }
            
            PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'settings_submit_success_message'));
            PEEP::getApplication()->redirect(urldecode($_POST['back_url']));
        }
    }
}