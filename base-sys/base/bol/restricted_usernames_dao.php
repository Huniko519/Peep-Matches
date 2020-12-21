<?php

class BOL_RestrictedUsernamesDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var BOL_RestrictedUsernamesDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RestrictedUsernamesDao
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
        return 'BOL_RestrictedUsernames';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_restricted_usernames';
    }

    /**
     * @param BOL_RestrictedUsernames $restricted_username
     */
    public function addRestrictedUsername( BOL_RestrictedUsernames $restricted_username )
    {
        $this->save($restricted_username);
    }

    /**
     *
     * @param string $username
     * @return BOL_RestrictedUsernames
     */
    public function getRestrictedUsername( $username )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('username', $username);
        return $this->findIdByExample($ex);
    }

    /**
     *
     * Remove username from restricted list
     * @param string $username
     */
    public function deleteRestrictedUsername( $username )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('username', $username);
        $this->deleteByExample($ex);
    }

    /**
     * @param string $username
     * @return BOL_RestrictedUsernamesDao
     */
    public function findRestrictedUsername( $username )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('username', $username);

        return $this->findObjectByExample($example);
    }

    /**
     * Get list of restricted usernames
     * @return array
     */
    public function getRestrictedUsernameList()
    {
        $ex = new PEEP_Example();
        $ex->setOrder('`username`');

        return $this->findListByExample($ex);
    }
}