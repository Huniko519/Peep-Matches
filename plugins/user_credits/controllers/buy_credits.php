<?php

class USERCREDITS_CTRL_BuyCredits extends PEEP_ActionController
{

    public function index()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        $form = new BuyCreditsForm();
        $this->addForm($form);

        $creditService = USERCREDITS_BOL_CreditsService::getInstance();
        
        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $lang = PEEP::getLanguage();
            $userId = PEEP::getUser()->getId();
            
            $billingService = BOL_BillingService::getInstance();

            if ( empty($values['gateway']['url']) || empty($values['gateway']['key']) 
                    || !$gateway = $billingService->findGatewayByKey($values['gateway']['key'])
                    || !$gateway->active )
            {
                PEEP::getFeedback()->error($lang->text('base', 'billing_gateway_not_found'));
                $this->redirect();
            }
            
            if ( !$pack = $creditService->findPackById($values['pack']) )
            {
                PEEP::getFeedback()->error($lang->text('usercredits', 'pack_not_found'));
                $this->redirect();
            }

            // create pack product adapter object
            $productAdapter = new USERCREDITS_CLASS_UserCreditsPackProductAdapter();
            
            // sale object
            $sale = new BOL_BillingSale();
            $sale->pluginKey = 'usercredits';
            $sale->entityDescription = strip_tags($creditService->getPackTitle($pack->price, $pack->credits));
            $sale->entityKey = $productAdapter->getProductKey();
            $sale->entityId = $pack->id;
            $sale->price = floatval($pack->price);
            $sale->period = 30;
            $sale->userId = $userId ? $userId : 0;
            $sale->recurring = 0;
    
            $saleId = $billingService->initSale($sale, $values['gateway']['key']);
    
            if ( $saleId )
            {
                // sale Id is temporarily stored in session
                $billingService->storeSaleInSession($saleId);
                $billingService->setSessionBackUrl($productAdapter->getProductOrderUrl());
    
                // redirect to gateway form page 
                PEEP::getApplication()->redirect($values['gateway']['url']);
            }
        }

        $lang = PEEP::getLanguage();

        $accountTypeId = $creditService->getUserAccountTypeId(PEEP::getUser()->getId());
        $packs = $creditService->getPackList($accountTypeId);
        $this->assign('packs', $packs);
        
        $this->setPageHeading($lang->text('usercredits', 'buy_credits_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_user');
        PEEP::getDocument()->setTitle($lang->text('usercredits', 'meta_title_buy_credits'));
        
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'dashboard');
    }
}

/**
 * Buy credits form class
 */
class BuyCreditsForm extends Form
{

    public function __construct()
    {
        parent::__construct('buy-credits-form');

        $accountTypeId = USERCREDITS_BOL_CreditsService::getInstance()->getUserAccountTypeId(PEEP::getUser()->getId());
        $packs = USERCREDITS_BOL_CreditsService::getInstance()->getPackList($accountTypeId);
        
        $packField = new RadioField('pack');
        $packField->setRequired();
        $value = 0;
        foreach ( $packs as $p )
        {
            $packField->addOption($p['id'], $p['title']);
            if ( $value == 0 )
            {
                $value = $p['id'];
            }
        }
        $packField->setValue($value);
        $this->addElement($packField);

        $gatewaysField = new BillingGatewaySelectionField('gateway');
        $gatewaysField->setRequired(true);
        $this->addElement($gatewaysField);

        $submit = new Submit('buy');
        $submit->setValue(PEEP::getLanguage()->text('base', 'checkout'));
        $this->addElement($submit);
    }
}