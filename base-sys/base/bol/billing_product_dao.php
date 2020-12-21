<?php

class BOL_BillingProductDao extends PEEP_BaseDao
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
     * @var BOL_BillingProductDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingProductDao
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
        return 'BOL_BillingProduct';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_billing_product';
    }

    public function deleteProduct( $productKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('productKey', $productKey);

        return $this->deleteByExample($example);
    }
    
    public function findByKey( $productKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('productKey', $productKey);
        
        return $this->findObjectByExample($example);
    }
}