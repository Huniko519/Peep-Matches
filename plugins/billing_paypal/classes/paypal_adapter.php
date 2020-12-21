<?php

class BILLINGPAYPAL_CLASS_PaypalAdapter implements PEEP_BillingAdapter
{
    const GATEWAY_KEY = 'billingpaypal';

    /**
     * @var BOL_BillingService
     */
    private $billingService;

    public function __construct()
    {
        $this->billingService = BOL_BillingService::getInstance();
    }

    public function prepareSale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    public function verifySale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    /**
     * (non-PHPdoc)
     * @see peep_core/PEEP_BillingAdapter#getFields($params)
     */
    public function getFields( $params = null )
    {
        $router = PEEP::getRouter();

        return array(
            'return_url' => $router->urlForRoute('billing_paypal_completed'),
            'cancel_return_url' => $router->urlForRoute('billing_paypal_canceled'),
            'notify_url' => PEEP::getRouter()->urlForRoute('billing_paypal_notify'),
            'business' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'business'),
            'formActionUrl' => $this->getOrderFormActionUrl()
        );
    }

    /**
     * (non-PHPdoc)
     * @see peep_core/PEEP_BillingAdapter#getOrderFormUrl()
     */
    public function getOrderFormUrl()
    {
        return PEEP::getRouter()->urlForRoute('billing_paypal_order_form');
    }

    /**
     * (non-PHPdoc)
     * @see peep_core/PEEP_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl()
    {
        $plugin = PEEP::getPluginManager()->getPlugin('billingpaypal');

        return $plugin->getStaticUrl() . 'img/paypal_logo.png';
    }

    /**
     * Returns Paypal gateway script url (sandbox or live)
     * 
     * @return string
     */
    private function getOrderFormActionUrl()
    {
        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        return $sandboxMode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
    }

    /**
     * Posts data back to PayPal for order verification
     *  
     * @param array $post
     * @return boolean
     */
    public function isVerified( $post )
    {
        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');
        $hostname = $sandboxMode ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        $nvpStr = '';
        foreach ( $post as $key => $value )
        {
            $value = urlencode(stripslashes($value));
            $nvpStr .= "$key=$value&";
        }
        $nvpStr .= 'cmd=_notify-validate';

        $str = file_get_contents('https://' . $hostname . '/cgi-bin/webscr?' . $nvpStr);

        return mb_strstr($str, 'VERIFIED') !== false;
    }
}