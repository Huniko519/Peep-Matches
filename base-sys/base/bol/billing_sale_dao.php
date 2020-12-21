<?php

class BOL_BillingSaleDao extends PEEP_BaseDao
{

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    const STATUS_INIT = 'init';
    const STATUS_PREPARED = 'prepared';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ERROR = 'error';

    const INIT_SALES_EXPIRE_INTERVAL = 432000; // 5 days
    const PREPARED_SALES_EXPIRE_INTERVAL = 2592000; // 30 days
    /**
     * Singleton instance.
     *
     * @var BOL_BillingSaleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingSaleDao
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
        return 'BOL_BillingSale';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_billing_sale';
    }

    /**
     * Finds sale by hash
     * 
     * @param $hash
     * @return BOL_BillingSale
     */
    public function findByHash( $hash )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('hash', $hash);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds sale by transaction Id
     * 
     * @param $transId
     * @param $gatewayId
     * @return mixed
     */
    public function findByGatewayTransactionId( $transId, $gatewayId = null )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('transactionUid', $transId);

        if ( !empty($gatewayId) )
        {
            $example->andFieldEqual('gatewayId', $gatewayId);
        }

        return $this->findObjectByExample($example);
    }

    /**
     * Expire sales with 'init' status
     * 
     * @return boolean
     */
    public function expireInitSales()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('status', self::STATUS_INIT);
        $example->andFieldLessThan('timeStamp', time() - self::INIT_SALES_EXPIRE_INTERVAL);

        $this->deleteByExample($example);
    }

    /**
     * Expire sales with 'prepared' status
     * 
     * @return boolean
     */
    public function expirePreparedSales()
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('status', self::STATUS_PREPARED);
        $example->andFieldLessThan('timeStamp', time() - self::PREPARED_SALES_EXPIRE_INTERVAL);

        $this->deleteByExample($example);
    }
    
    public function getSaleList( $page, $onPage )
    {
        $first = ($page - 1 ) * $onPage;
        
        $gatewayDao = BOL_BillingGatewayDao::getInstance();
        $pluginDao = BOL_PluginDao::getInstance();
        
        $sql = "SELECT `s`.*, `gw`.`gatewayKey`, `p`.`title` AS `pluginTitle` 
            FROM `".$this->getTableName()."` AS `s`
            LEFT JOIN `".$gatewayDao->getTableName()."` AS `gw` ON (`s`.`gatewayId` = `gw`.`id`)
            LEFT JOIN `".$pluginDao->getTableName()."` AS `p` ON (`s`.`pluginKey` = `p`.`key`)
            WHERE `s`.`status` = 'delivered'
            ORDER BY `timeStamp` DESC
            LIMIT :first, :limit";
        
        return $this->dbo->queryForList($sql, array('first' => $first, 'limit' => $onPage));
    }
    
    public function getSalesCurrencies()
    {
        $sql = "SELECT DISTINCT(`currency`) 
            FROM `".$this->getTableName()."`";
        
        return $this->dbo->queryForList($sql);
    }
    
    public function getSalesSumByCurrency( $currency )
    {
        $sql = "SELECT SUM(`totalAmount`) 
            FROM `".$this->getTableName()."`
            WHERE `currency` = :curr
            AND `status` = 'delivered'";
        
        return $this->dbo->queryForColumn($sql, array('curr' => $currency));
    }
    
    public function countSales( )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('status', 'delivered');
        
        return $this->countByExample($example);
    }
}