<?php

class BOL_RemoteAuthDao extends PEEP_BaseDao
{
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Singleton instance.
     *
     * @var BOL_RemoteAuthDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RemoteAuthDao
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
        return 'BOL_RemoteAuth';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_remote_auth';
    }
    
    /**
     * 
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByRemoteId( $remoteId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('remoteId', $remoteId);
        
        return $this->findObjectByExample($example); 
    }
    
    /**
     * 
     * @param $remoteId
     * @return BOL_RemoteAuth
     */
    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->findObjectByExample($example); 
    }
    
    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        
        return $this->deleteByExample($example); 
    }
    
    public function saveOrUpdate( BOL_RemoteAuth $entity )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('remoteId', $entity->remoteId);
        $example->andFieldEqual('userId', $entity->userId);
        
        $entityDto = $this->findObjectByExample($example);
        if ( $entityDto !== null )
        {
            $entity = $entityDto;
        }
        
        return $this->save($entity);
    }
}