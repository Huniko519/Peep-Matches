<?php

class BOL_FlagService
{
    /*
     * @type BOL_FlagDao
     */
    private $flagDao;
    /**
     *
     * @var BOL_FlagService
     */
    private static $classInstance;

    /**
     * Enter description here...
     *
     * @return BOL_FlagService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {
        $this->flagDao = BOL_FlagDao::getInstance();
    }

    public function addFlag( $entityType, $entityId, $reason, $userId )
    {
        $flagDto = $this->flagDao->findFlag($entityType, $entityId, $userId);
        
        if ( $flagDto === null )
        {
            $flagDto = new BOL_Flag;
        }
        
        $flagDto->userId = $userId;
        $flagDto->entityType = $entityType;
        $flagDto->entityId = $entityId;
        $flagDto->reason = $reason;
        $flagDto->timeStamp = time();
        
        $this->flagDao->save($flagDto);
    }

    public function isFlagged( $entityType, $entityId, $userId )
    {
        return $this->findFlag($entityType, $entityId, $userId) !== null;
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return BOL_Flag
     */
    public function findFlag( $entityType, $entityId, $userId )
    {
        return $this->flagDao->findFlag($entityType, $entityId, $userId);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findFlagsByEntityTypeList( $entityTypes, array $limit = null )
    {
        return $this->flagDao->findByEntityTypeList($entityTypes, $limit);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return int
     */
    public function findCountForEntityTypeList( $entityTypes )
    {
        return $this->flagDao->findCountForEntityTypeList($entityTypes);
    }
    
    public function getContentGroupsWithCount()
    {
        $contentTypes = $this->getContentTypeListWithCount();
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups(array_keys($contentTypes));
        
        foreach ( $contentGroups as &$group )
        {
            $group["url"] = PEEP::getRouter()->urlForRoute("base.moderation_flags", array(
                "group" => $group["name"]
            ));
            
            $group["count"] = 0;
            foreach ( $group["entityTypes"] as $entityType )
            {
                $group["count"] += $contentTypes[$entityType]["count"];
            }
        }
        
        return $contentGroups;
    }
    
    public function getContentTypeListWithCount()
    {
        $contentTypes = BOL_ContentService::getInstance()->getContentTypes();
        $entityTypes = array_keys($contentTypes);
        $counts = $this->findCountForEntityTypeList($entityTypes);
        
        $out = array();
        
        foreach ( $counts as $entityType => $count )
        {
            if ( !PEEP::getUser()->isAuthorized($contentTypes[$entityType]["authorizationGroup"]) )
            {
                continue;
            }
            
            $out[$entityType] = $contentTypes[$entityType];
            $out[$entityType]["count"] = $count;
        }
        
        return $out;
    }
    
    public function deleteFlagList($entityType, array $entityIdList = null)
    {
    	$this->flagDao->deleteFlagList($entityType, $entityIdList);
    }
    
    public function deleteEntityFlags( $entityType, $entityId )
    {
        $this->flagDao->deleteEntityFlags($entityType, $entityId);
    }
    
    public function deleteFlagListByIds( $idList )
    {
        $this->flagDao->deleteByIdList($idList);
    }

    
    /* Backward compatibility methods */
    
    /**
     * 
     * @param type $type
     * @param type $entityId
     */
    public function deleteByTypeAndEntityId( $type, $entityId )
    {
        $this->deleteEntityFlags($type, $entityId);
    }
    
    public function deleteByType( $entityType )
    {
        $this->deleteFlagList($entityType);
    }
}