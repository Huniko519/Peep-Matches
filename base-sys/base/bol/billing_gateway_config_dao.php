<?php

class BOL_BillingGatewayConfigDao extends PEEP_BaseDao
{

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_BillingGatewayConfigDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingGatewayConfigDao
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
        return 'BOL_BillingGatewayConfig';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_billing_gateway_config';
    }

    public function getConfig( $gatewayKey, $name )
    {
        if ( !mb_strlen($gatewayKey) || !mb_strlen($name) )
        {
            return null;
        }
        
        $gateway = BOL_BillingGatewayDao::getInstance()->findByKey($gatewayKey);

        if ( $gateway )
        {
            $example = new PEEP_Example();
            $example->andFieldEqual('gatewayId', $gateway->id);
            $example->andFieldEqual('name', $name);

            return $this->findObjectByExample($example);
        }

        return null;
    }

    public function getConfigValue( $gatewayKey, $name )
    {
        if ( !mb_strlen($gatewayKey) || !mb_strlen($name) )
        {
            return null;
        }

        $gateway = BOL_BillingGatewayDao::getInstance()->findByKey($gatewayKey);

        if ( $gateway )
        {
            $example = new PEEP_Example();
            $example->andFieldEqual('gatewayId', $gateway->id);
            $example->andFieldEqual('name', $name);

            $conf = $this->findObjectByExample($example);
            return $conf ? $conf->value : null;
        }

        return null;
    }

    public function setConfigValue( $gatewayKey, $name, $value )
    {
        if ( !mb_strlen($gatewayKey) || !mb_strlen($name) )
        {
            return false;
        }

        $config = $this->getConfig($gatewayKey, $name);

        if ( $config )
        {
            $config->value = $value;
            $this->save($config);

            return true;
        }

        return false;
    }
    
    public function addConfig( $gatewayKey, $name, $value )
    {
        if ( !mb_strlen($gatewayKey) || !mb_strlen($name) )
        {
            return false;
        }
        
        $gateway = BOL_BillingGatewayDao::getInstance()->findByKey($gatewayKey);

        if ( $gateway )
        {
            $config = new BOL_BillingGatewayConfig();
            $config->gatewayId = $gateway->id;
            $config->name = $name;
            $config->value = $value;
            
            $this->save($config);
            
            return true;
        }
        
        return false;
    }
    
    public function deleteConfig( $gatewayKey, $name )
    {
        if ( !mb_strlen($gatewayKey) || !mb_strlen($name) )
        {
            return false;
        }
        
        $config = BOL_BillingGatewayConfigDao::getInstance()->getConfig($gatewayKey, $name);

        if ( $config )
        {
            $this->deleteById($config->id);
            
            return true;
        }
        
        return false;
    }
}