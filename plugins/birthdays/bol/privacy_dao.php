<?php

class BIRTHDAYS_BOL_PrivacyDao extends PEEP_BaseDao
{
    /**
     * @var BIRTHDAYS_BOL_PrivacyDao
     */
    private $userDao;
    /**
     * Singleton instance.
     *
     * @var BIRTHDAYS_BOL_PrivacyDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BIRTHDAYS_BOL_PrivacyDao
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
        return 'BIRTHDAYS_BOL_Privacy';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'birthdays_privacy';
    }

    /**
     * @param int $userId
     * @return BIRTHDAYS_BOL_Privacy
     */

    public function findByUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', (int)$userId);

        return $this->findObjectByExample($ex);
    }

    /**
     * @param int $userId
     */

    public function deleteByUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', (int)$userId);

        return $this->deleteByExample($ex);
    }
}