<?php

class BOL_UserFeaturedDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var BOL_UserFeaturedDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserFeaturedDao
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
        return 'BOL_UserFeatured';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_user_featured';
    }

    public function findByUserId( $id )
    {
        $ex = new PEEP_Example();

        $ex->andFieldEqual('userId', $id);

        return $this->findObjectByExample($ex);
    }

    public function deleteByUserId( $userId )
    {
        $ex = new PEEP_Example();

        $ex->andFieldEqual('userId', $userId);

        $this->deleteByExample($ex);
    }
}