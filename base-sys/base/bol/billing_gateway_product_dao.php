<?php

class BOL_BillingGatewayProductDao extends PEEP_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_BillingGatewayProductDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingGatewayProductDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_BillingGatewayProduct';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_billing_gateway_product';
    }
    
    public function findListForGateway( $gatewayId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('gatewayId', $gatewayId);
        
        return $this->findListByExample($example);
    }
    
    public function findProduct( $gatewayId, $pluginKey, $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('gatewayId', $gatewayId);
        $example->andFieldEqual('pluginKey', $pluginKey);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);
        
        return $this->findObjectByExample($example);
    }
    
    public function deleteByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('pluginKey', $pluginKey);
        
        return $this->deleteByExample($example);
    }
}