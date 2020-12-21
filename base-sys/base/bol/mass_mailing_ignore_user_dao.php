<?php

class BOL_MassMailingIgnoreUserDao extends PEEP_BaseDao
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
     * @var BOL_MassMailingIgnoreUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MassMailingIgnoreUserDao
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
        return 'BOL_MassMailingIgnoreUser';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_mass_mailing_ignore_user';
    }

    /**
     * @param int $userId
     * @return BOL_MassMailingIgnoreUser
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', (int) $userId);

        return $this->findObjectByExample($example);
    }
}