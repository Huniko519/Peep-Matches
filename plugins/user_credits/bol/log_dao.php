<?php

class USERCREDITS_BOL_LogDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var USERCREDITS_BOL_LogDao
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
     * @return USERCREDITS_BOL_LogDao
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
        return 'USERCREDITS_BOL_Log';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'usercredits_log';
    }
    
    /**
     * Finds user last action log
     * 
     * @param int $userId
     * @param int $actionId
     * @return USERCREDITS_BOL_Log
     */
    public function findLast( $userId, $actionId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('actionId', $actionId);
        $example->setOrder('`logTimestamp` DESC');
        $example->setLimitClause(0, 1);
        
        return $this->findObjectByExample($example);
    }

    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array
     */
    public function findListForUser( $userId, $page, $limit )
    {
        $actionDao = USERCREDITS_BOL_ActionDao::getInstance();

        $start = ($page - 1) * $limit;
        $sql =
            'SELECT `l`.*, `a`.`pluginKey`, `a`.`actionKey` FROM `'.$this->getTableName().'` AS `l`
            INNER JOIN `'.$actionDao->getTableName().'` AS `a` ON (`a`.`id` = `l`.`actionId`)
            WHERE `l`.`userId` = :uid
            ORDER BY `l`.`logTimestamp` DESC
            LIMIT :start, :limit';

        return $this->dbo->queryForList($sql, array('uid' => $userId, 'start' => $start, 'limit' => $limit));
    }

    /**
     * @param $userId
     * @return int
     */
    public function countEntriesForUser( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->countByExample($example);
    }
    
    public function deleteUserCreditLogByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example);
    }
}