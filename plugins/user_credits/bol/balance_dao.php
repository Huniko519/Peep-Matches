<?php

class USERCREDITS_BOL_BalanceDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_BalanceDao
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return USERCREDITS_BOL_BalanceDao
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
        return 'USERCREDITS_BOL_Balance';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'usercredits_balance';
    }
    
    /**
     * Finds user balance
     * 
     * @param int $userId
     * @return USERCREDITS_BOL_Balance
     */
    public function findByUserId( $userId )
    {
    	$example = new PEEP_Example();
    	$example->andFieldEqual('userId', $userId);
    	
    	return $this->findObjectByExample($example);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getBalanceForUserList( $ids )
    {
        if ( !count($ids) )
        {
            return array();
        }

        $ids = array_unique($ids);
        $example = new PEEP_Example();
        $example->andFieldInArray('userId', $ids);

        return $this->findListByExample($example);
    }
    
    public function deleteUserCreditBalanceByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example);
    }
}