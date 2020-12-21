<?php

class BOL_AuthTokenDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const TOKEN = 'token';
    const TIME_STAMP = 'timeStamp';

    /**
     * Singleton instance.
     *
     * @var BOL_AuthTokenDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthTokenDao
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
        return 'BOL_AuthToken';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_auth_token';
    }

    /**
     * @param string $token
     * @return integer
     */
    public function findUserIdByToken( $token )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::TOKEN, trim($token));

        $dto = $this->findObjectByExample($example);

        return $dto === null ? 0 : $dto->getUserId();
    }

    /**
     * @param integer $userId
     */
    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, $userId);

        $this->deleteByExample($example);
    }
}
