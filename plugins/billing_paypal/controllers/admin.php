<?php

class BILLINGPAYPAL_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $language = PEEP::getLanguage();

        $paypalConfigForm = new PaypalConfigForm();
        $this->addForm($paypalConfigForm);

        if ( PEEP::getRequest()->isPost() && $paypalConfigForm->isValid($_POST) )
        {
            $res = $paypalConfigForm->process();
            PEEP::getFeedback()->info($language->text('billingpaypal', 'settings_updated'));
            $this->redirect();
        }

        $adapter = new BILLINGPAYPAL_CLASS_PaypalAdapter();
        

        $gateway = $billingService->findGatewayByKey(BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);

        $this->setPageHeading(PEEP::getLanguage()->text('billingpaypal', 'config_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_app');
    }
}

class PaypalConfigForm extends Form
{

    public function __construct()
    {
        parent::__construct('paypal-config-form');

        $language = PEEP::getLanguage();
        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY;

        $business = new TextField('business');
        $business->setValue($billingService->getGatewayConfigValue($gwKey, 'business'));
        $this->addElement($business);

        $sandboxMode = new CheckboxField('sandboxMode');
        $sandboxMode->setValue($billingService->getGatewayConfigValue($gwKey, 'sandboxMode'));
        $this->addElement($sandboxMode);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('billingpaypal', 'btn_save'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $billingService = BOL_BillingService::getInstance();
        $gwKey = BILLINGPAYPAL_CLASS_PaypalAdapter::GATEWAY_KEY;

        $billingService->setGatewayConfigValue($gwKey, 'business', $values['business']);
        $billingService->setGatewayConfigValue($gwKey, 'sandboxMode', $values['sandboxMode']);
    }
}