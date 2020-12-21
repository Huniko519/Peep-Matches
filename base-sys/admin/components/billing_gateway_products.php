<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CMP_BillingGatewayProducts extends PEEP_Component
{
    public function __construct( $params = array() )
    {
        parent::__construct();

        $service = BOL_BillingService::getInstance();
        
        $gateway = $service->findGatewayByKey($params['gateway']);
        
        if ( !$gateway || $gateway->dynamic )
        {
            $this->setVisible(false);
            return;
        }
                
        $event = new BASE_CLASS_EventCollector('base.billing_add_gateway_product');
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();
        
        $eventProducts = array();
        if ( $data )
        {
            foreach ( $data as $plugin )
            {
                foreach ( $plugin as $product )
                {
                    $id = $service->addGatewayProduct($gateway->id, $product['pluginKey'], $product['entityType'], $product['entityId']);
                    $product['id'] = $id;
                    $eventProducts[] = $product;
                }
            }
        }
        
        $products = $service->findGatewayProductList($gateway->id);
        
        foreach ( $eventProducts as &$prod )
        {
            $prod['productId'] = !empty($products[$prod['id']]) ? $products[$prod['id']]['dto']->productId : null;
            $prod['plugin'] = !empty($products[$prod['id']]) ? $products[$prod['id']]['plugin'] : null;
        }
        
        $this->assign('products', $eventProducts);
        
        $this->assign('actionUrl', PEEP::getRouter()->urlFor('BASE_CTRL_Billing', 'saveGatewayProduct'));
        $this->assign('backUrl', urlencode(PEEP::getRouter()->getBaseUrl() . PEEP::getRouter()->getUri()));
    }
}