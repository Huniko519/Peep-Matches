<?php

class USERCREDITS_BOL_PackDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_PackDao
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
     * @return USERCREDITS_BOL_PackDao
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
        return 'USERCREDITS_BOL_Pack';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'usercredits_pack';
    }
    
    /**
     * Returns list of packs for account type
     *
     * @param $accountTypeId
     * @return array
     */
    public function getAllPacks( $accountTypeId = null )
    {
        $example = new PEEP_Example();
        if ( $accountTypeId )
        {
            $example->andFieldEqual('accountTypeId', $accountTypeId);
        }
        $example->setOrder('`price` ASC');
        
        return $this->findListByExample($example);
    }
}