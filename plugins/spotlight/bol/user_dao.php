<?php

class SPOTLIGHT_BOL_UserDao extends PEEP_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Class instance
     *
     * @var SPOTLIGHT_BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MessageDao
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
        return 'SPOTLIGHT_BOL_User';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'spotlight_user';
    }

    public function clearExpiredUsers()
    {
        $query = "DELETE FROM `" . $this->getTableName() . "` WHERE `expiration_timestamp` <= ".time();
        $this->dbo->query($query);
    }
    
    public function findExpiredUsers()
    {
        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE `expiration_timestamp` <= ".time();

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
    
    public function findSpotLight( $start = 0, $count = null )
    {
        $example = new PEEP_Example();
        $example->setOrder("`timestamp` DESC");
        
        if ( $count !== null )
        {
            $example->setLimitClause($start, $count);
        }

        return $this->findListByExample($example);
    }

    public function deleteByUserId($userId)
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        $this->deleteByExample($example);
    }

    public function findUserById($userId)
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findIdByExample($example);
    }
}
