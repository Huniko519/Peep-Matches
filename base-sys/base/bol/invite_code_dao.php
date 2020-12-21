<?php

class BOL_InviteCodeDao extends PEEP_BaseDao
{
    const CODE = 'code';
    const EXP_TIME = 'expiration_stamp';
    const USER_ID = 'userId';

    /**
     * Singleton instance.
     *
     * @var BOL_ComponentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_InviteCode';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_invite_code';
    }

    public function findByCode( $code )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::CODE, trim($code));

        return $this->findObjectByExample($example);
    }
    
    public function deleteByCode( $code )
    {
        if ( empty($code) )
        {
            return;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::CODE, trim($code));

        return $this->deleteByExample($example);
    }
}