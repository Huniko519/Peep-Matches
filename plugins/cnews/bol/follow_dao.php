<?php

class CNEWS_BOL_FollowDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_FollowDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_FollowDao
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
        return 'CNEWS_BOL_Follow';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_follow';
    }

    public function addFollow( $userId, $feedType, $feedId, $permission = CNEWS_BOL_Service::PRIVACY_EVERYBODY )
    {
        $dto = $this->findFollow($userId, $feedType, $feedId, $permission);

        if ( $dto === null )
        {
            $dto = new CNEWS_BOL_Follow();
            $dto->feedType = $feedType;
            $dto->feedId = $feedId;
            $dto->userId = $userId;
            $dto->followTime = time();
        }

        $dto->permission = $permission;
        $this->save($dto);

        return $dto;
    }

    public function findFollow( $userId, $feedType, $feedId, $permission = CNEWS_BOL_Service::PRIVACY_EVERYBODY )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->findObjectByExample($example);
    }

    public function findFollowByFeedList( $userId, $feedList , $permission = CNEWS_BOL_Service::PRIVACY_EVERYBODY )
    {
        if ( empty($feedList) )
        {
            return array();
        }

        $where = array();
        foreach ( $feedList as $feed )
        {
            $perm = empty($feed["permission"]) ? $permission : $feed["permission"];
            $permWhere = empty($perm) ? "1" : 'permission="' . $this->dbo->escapeString($perm) . '"';
            
            $where[] = '(`feedType`="' . $this->dbo->escapeString($feed["feedType"]) 
                    . '" AND `feedId`="' . $this->dbo->escapeString($feed["feedId"]) 
                    . '" AND ' . $permWhere . ' )';
        }

        $query = "SELECT * FROM " . $this->getTableName() . " WHERE `userId`=:u AND ( " . implode(" OR ", $where) . " )";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "u" => $userId
        ));
    }

    public function findList( $feedType, $feedId, $permission = null )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->findListByExample($example);
    }

    public function removeFollow( $userId, $feedType, $feedId, $permission = null )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        if ( !empty($permission) )
        {
            $example->andFieldEqual('permission', $permission);
        }

        return $this->deleteByExample($example);
    }
}