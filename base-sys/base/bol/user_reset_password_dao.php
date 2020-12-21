<?php

class BOL_UserResetPasswordDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const CODE = 'code';
    const EXPIRATION_TS = 'expirationTimeStamp';
    const UPDATE_TS = 'updateTimeStamp';

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
     * @var UserSuspendDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UserSuspendDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_reset_password';
    }

    public function getDtoClassName()
    {
        return 'BOL_UserResetPassword';
    }

    /**
     * @param integer $userId
     * @return BOL_UserResetPassword
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int)$userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $code
     * @return BOL_UserResetPassword
     */
    public function findByCode( $code )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::CODE, $code);

        return $this->findObjectByExample($example);
    }

    public function deleteExpiredEntities()
    {
        $example = new PEEP_Example();
        $example->andFieldLessOrEqual(self::EXPIRATION_TS, time());

        $this->deleteByExample($example);
    }
}