<?php

class PEEP_RemoteAuthAdapter extends PEEP_AuthAdapter 
{
    private $remoteId;
    private $type;
    
    /**
     * 
     * @var BOL_RemoteAuthService
     */
    private $remoteAuthService;
    
    public function __construct($remoteId, $type)
    {
        $this->remoteId = $remoteId;
        $this->type = trim($type);
        
        $this->remoteAuthService = BOL_RemoteAuthService::getInstance();
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getRemoteId()
    {
        return $this->remoteUserId;
    }
    
    public function isRegistered()
    {
        return $this->remoteAuthService->findByRemoteId($this->remoteId);
    }
    
    public function register( $userId, $custom = null )
    {
        $entity = new BOL_RemoteAuth();
        $entity->userId = (int) $userId;
        $entity->remoteId = $this->remoteId;
        $entity->type = $this->type;
        $entity->timeStamp = time();
        $entity->custom = $custom;

        return $this->remoteAuthService->saveOrUpdate($entity);
    }
    
    /**
     *
     * @return PEEP_AuthResult
     */
    public function authenticate()
    {
        $entity = $this->remoteAuthService->findByRemoteId($this->remoteId);
        if ( $entity === null )
        {
            $userId = null;
            $code = PEEP_AuthResult::FAILURE;
        }
        else
        {
            $userId = (int) $entity->userId;
            $code = PEEP_AuthResult::SUCCESS;
        }
          
        return new PEEP_AuthResult($code, $userId);
    }
}