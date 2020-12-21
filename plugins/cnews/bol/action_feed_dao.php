<?php

class CNEWS_BOL_ActionFeedDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_ActionFeedDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_ActionFeedDao
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
        return 'CNEWS_BOL_ActionFeed';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_action_feed';
    }

    public function addIfNotExists( CNEWS_BOL_ActionFeed $dto )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('activityId', $dto->activityId);
        $example->andFieldEqual('feedId', $dto->feedId);
        $example->andFieldEqual('feedType', $dto->feedType);

        $existingDto = $this->findObjectByExample($example);

        if ( $existingDto === null )
        {
            $this->save($dto);
        }
        else
        {
            $dto->id = $existingDto->id;
        }
    }

    public function deleteByFeedAndActivityId( $feedType, $feedId, $activityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('activityId', $activityId);
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);

        $this->deleteByExample($example);
    }

    public function deleteByActivityIds( $activityIds )
    {
        if ( empty($activityIds) )
        {
            return;
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('activityId', $activityIds);

        $this->deleteByExample($example);
    }
    
    public function findByActivityIds( $activityIds )
    {
        if ( empty($activityIds) )
        {
            return array();
        }
        
        $example = new PEEP_Example();
        $example->andFieldInArray('activityId', $activityIds);

        return $this->findListByExample($example);
    }
    
    public function findByFeed( $feedType, $feedId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('feedType', $feedType);
        $example->andFieldEqual('feedId', $feedId);

        return $this->findListByExample($example);
    }
}