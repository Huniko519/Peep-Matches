<?php

class PVISITORS_BOL_VisitorDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var PVISITORS_BOL_VisitorDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return PVISITORS_BOL_VisitorDao
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
     */
    public function getDtoClassName()
    {
        return 'PVISITORS_BOL_Visitor';
    }
    
    /**
     * @see PEEP_BaseDao::getTableName()
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'pvisitors_visitor';
    }
    
    /**
     * @param $userId
     * @param $visitorId
     * @return mixed
     */
    public function findVisitor( $userId, $visitorId )
    {
    	$example = new PEEP_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->andFieldEqual('visitorId', $visitorId);
    	
    	return $this->findObjectByExample($example);
    }
    
    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array|mixed
     */
    public function findUserVisitors( $userId, $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$example = new PEEP_Example();
    	$example->andFieldEqual('userId', $userId);
    	$example->setLimitClause($first, $limit);
    	$example->setOrder('`visitTimestamp` DESC');
    	
    	return $this->findListByExample($example);
    }
    
    public function setViewedStatusByVisitorIds( $userId, $visitorIds, $viewed = true  )
    {
        if ( empty($visitorIds) )
        {
            return;
        }
        
        $query = "UPDATE " . $this->getTableName() . " SET `viewed`=:viewed "
                . "WHERE `visitorId` IN ( " . implode(",", $visitorIds) . " ) "
                    . "AND `userId`=:u";
        
        $this->dbo->query($query, array(
            "u" => $userId,
            "viewed" => $viewed
        ));

        return true;
    }
    
    public function getViewedStatusByVisitorIds( $userId, $visitorIds  )
    {
        $dtoList = $this->findVisitorsByVisitorIds($userId, $visitorIds);
        
        $out = array();
        foreach ( $dtoList as $dto )
        {
            $out[$dto->visitorId] = $dto->viewed;
        }
        
        return $out;
    }
    
    public function findVisitorsByVisitorIds( $userId, $visitorIds  )
    {
        if ( empty($visitorIds) )
        {
            return array();
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual("userId", $userId);
        $example->andFieldInArray("visitorId", $visitorIds);
        $example->setOrder("visitTimestamp DESC");
        
        return $this->findListByExample($example);
    }
    
    /**
     * @param $userId
     * @param $page
     * @param $limit
     * @return array|mixed
     */
    public function findVisitorUsers( $userId, $page, $limit )
    {
    	$first = ( $page - 1 ) * $limit;
    	
    	$query = "SELECT `u`.*
            FROM `".$this->getTableName()."` AS `g`
            INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u` 
                ON (`g`.`visitorId` = `u`.`id`)
            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                ON( `u`.`id` = `s`.`userId` )
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `s`.`id` IS NULL AND `d`.`id` IS NULL
            AND `g`.`userId` = ?
            ORDER BY `g`.`visitTimestamp` DESC
            LIMIT ?, ?";

        return $this->dbo->queryForObjectList($query, BOL_UserDao::getInstance()->getDtoClassName(), array($userId, $first, $limit));
    }
    
    /**
     * @param $userId
     * @return mixed|null|string
     */
    public function countUserVisitors( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->countByExample($example);
    }

    public function countNewVisitors( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('viewed', 0);

        return $this->countByExample($example);
    }
    
    /**
     * @param $timestamp
     */
    public function deleteExpired( $timestamp )
    {
    	$example = new PEEP_Example();
    	$example->andFieldLessThan('visitTimestamp', time() - $timestamp);
    	
    	$this->deleteByExample($example);
    }
    
    /**
     * @param $userId
     */
    public function deleteUserVisitors( $userId )
    {
    	$sql = "DELETE FROM `".$this->getTableName()."` 
    	   WHERE `userId` = ? OR `visitorId` = ?";
    	
    	$this->dbo->query($sql, array($userId, $userId));
    }
}
