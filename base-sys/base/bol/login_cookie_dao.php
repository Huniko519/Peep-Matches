<?php

class BOL_LoginCookieDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const COOKIE = 'cookie';

    /**
     * Singleton instance.
     *
     * @var BOL_LoginCookieDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_LoginCookieDao
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
        return 'BOL_LoginCookie';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_login_cookie';
    }

    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $cookie
     * @return BOL_LoginCookie
     */
    public function findByCookie( $cookie )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::COOKIE, $cookie);

        return $this->findObjectByExample($example);
    }
}