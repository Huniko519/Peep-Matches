<?php

class CNEWS_BOL_StatusDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_StatusDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_StatusDao
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
        return 'CNEWS_BOL_Status';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_status';
    }
    
    public function saveStatus( $feedType, $feedId, $status )
    {
        $dto = $this->removeStatus($feedType, $feedId);
        
        $dto = new CNEWS_BOL_Status();
        $dto->feedType = $feedType;
        $dto->feedId = $feedId;
        $dto->status = $status;
        $dto->timeStamp = time();
        
        $this->save($dto);
        
        return $dto;
    }
    
    /**
     * 
     * @param $feedType
     * @param $feedId
     * @return CNEWS_BOL_Status
     */
    public function findStatus( $feedType, $feedId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        return $this->findObjectByExample($example);
    }
    
    public function removeStatus( $feedType, $feedId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);
        
        return $this->deleteByExample($example);
    }
}