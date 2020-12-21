<?php

class BOL_BillingGatewayDao extends PEEP_BaseDao
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
     * @var BOL_BillingGatewayDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingGatewayDao
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
        return 'BOL_BillingGateway';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_billing_gateway';
    }

    public function findByKey( $key )
    {
        if ( !mb_strlen($key) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('gatewayKey', $key);

        return $this->findObjectByExample($example);
    }

    public function deleteByKey( $key )
    {
        if ( !mb_strlen($key) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('gatewayKey', $key);
        
        return $this->deleteByExample($example);
    }

    public function getActiveList()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('active', 1);
        $example->andFieldEqual('hidden', 0);

        return $this->findListByExample($example);
    }
    
    public function getNotDynamicList()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('dynamic', 0);
        $example->andFieldEqual('hidden', 0);
        
        return $this->findListByExample($example);
    }
}