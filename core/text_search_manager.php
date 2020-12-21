<?php

final class PEEP_TextSearchManager
{
    /**
     * Sort by date
     */
    CONST SORT_BY_DATE = BASE_CLASS_AbstractSearchStorage::SORT_BY_DATE;

    /**
     * Sort by relevance
     */
    CONST SORT_BY_RELEVANCE = BASE_CLASS_AbstractSearchStorage::SORT_BY_RELEVANCE;

    /**
     * Active entity status 
     */
    CONST ENTITY_STATUS_ACTIVE = BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_ACTIVE;

    /**
     * Not active entity status
     */
    CONST ENTITY_STATUS_NOT_ACTIVE = BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE;

    /**
     * Singleton instance.
     * @var PEEP_TextSearchManager
     */
    private static $classInstance;

    /**
     * Default storage instance     
     * @var BASE_CLASS_InterfaceSearchStorage
     */
    private $defaultStorageInstance;

    /**
     * Active storage instance     
     * @var BASE_CLASS_InterfaceSearchStorage
     */
    private $activeStorageInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_TextSearchManager
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
     * Constructor.
     */
    private function __construct()
    {
        $this->defaultStorageInstance = new BASE_CLASS_MysqlSearchStorage;
        $this->activeStorageInstance = null;
    }

    /**
     * Add entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @param string  $text
     * @param integer $timeStamp
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function addEntity( $entityType, $entityId, $text, $timeStamp, array $tags = array(), $status = null )
    {
        $this->defaultStorageInstance->addEntity($entityType, $entityId, $text, $timeStamp, $tags, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->addEntity($entityType, $entityId, $text, $timeStamp, $tags, $status);
        }
    }

    /**
     * Set entities status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function setEntitiesStatus( $entityType, $entityId, $status = self::ENTITY_STATUS_ACTIVE )
    {
        $this->defaultStorageInstance->setEntitiesStatus($entityType, $entityId, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->setEntitiesStatus($entityType, $entityId, $status);
        }
    }

    /**
     * Set entities status by tags
     * 
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function setEntitiesStatusByTags( array $tags, $status = self::ENTITY_STATUS_ACTIVE )
    {
        $this->defaultStorageInstance->setEntitiesStatusByTags($tags, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->setEntitiesStatusByTags($tags, $status);
        }
    }

    /**
     * Delete entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @throws Exception
     * @return void
     */
    public function deleteEntity( $entityType, $entityId )
    {
        $this->defaultStorageInstance->deleteEntity($entityType, $entityId);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteEntity($entityType, $entityId);
        }
    }

    /**
     * Delete all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function deleteAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->deleteAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteAllEntities($entityType);
        }
    }

    /**
     * Delete all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function deleteAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->deleteAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteAllEntitiesByTags($tags);
        }   
    }

    /**
     * Deactivate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function deactivateAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->deactivateAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deactivateAllEntities($entityType);
        }
    }
    
    /**
     * Deactivate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function deactivateAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->deactivateAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deactivateAllEntitiesByTags($tags);
        }
    }

    /**
     * Activate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function activateAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->activateAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->activateAllEntities($entityType);
        }
    }

    /**
     * Activate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function activateAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->activateAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->activateAllEntitiesByTags($tags);
        }
    }

    /**
     * Search entities
     *
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntities( $text, $first, $limit, 
            array $tags = array(), $sort = self::SORT_BY_RELEVANCE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sort, $sortDesc, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sort, $sortDesc, $timeStart, $timeEnd);
    }

    /**
     * Search entities count
     *
     * @param string $text
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return integer
     */
    public function searchEntitiesCount( $text, array $tags = array(), $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesCount($text, $tags, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                searchEntitiesCount($text, $tags, $timeStart, $timeEnd);
    }

    /**
     * Search entities by tags
     *
     * @param array $tags
     * @param integer $first
     * @param integer $limit     
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntitiesByTags( array $tags, $first, $limit, 
            $sort = self::SORT_BY_DATE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesByTags($tags, $first, $limit, $sort, $sortDesc, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                    searchEntitiesByTags($tags, $first, $limit, $sort, $sortDesc, $timeStart, $timeEnd);
    }

    /**
     * Search entities count by tags
     *
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return integer
     */
    public function searchEntitiesCountByTags( array $tags, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesCountByTags($tags, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                searchEntitiesCountByTags($tags, $timeStart, $timeEnd);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $entityType
     * @throws Exception
     * @return array
     */
    public function getAllEntities( $first, $limit, $entityType = null )
    {
        return $this->defaultStorageInstance->getAllEntities($first, $limit, $entityType);
    }
}
