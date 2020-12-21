<?php

class BASE_CLASS_MysqlSearchStorage extends BASE_CLASS_AbstractSearchStorage
{
    /**
     * Search entity dao
     * @var BOL_SearchEntityDao
     */
    private $searchEntityDao;

    /**
     * Search entity tag dao
     * @var BOL_SearchEntityTagDao
     */
    private $searchEntityTagDao;
    
    /**
     *  Class constructor
     */
    public function __construct() 
    {
        $this->searchEntityDao = BOL_SearchEntityDao::getInstance();
        $this->searchEntityTagDao = BOL_SearchEntityTagDao::getInstance();
    }

    /**
     * Add entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @param string $text
     * @param integer $timeStamp
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function addEntity( $entityType, $entityId, $text, $timeStamp, array $tags = array(), $status = null )
    {
        $dto = new BOL_SearchEntity;
        $dto->entityType = $entityType; 
        $dto->entityId   = $entityId;
        $dto->text = $this->cleanSearchText($text); 
        $dto->timeStamp = $timeStamp;
        $dto->activated = BOL_SearchEntityDao::ENTITY_ACTIVATED;
        $dto->status = !$status
                ? BOL_SearchEntityDao::ENTITY_STATUS_ACTIVE
                : $status;

        $this->searchEntityDao->save($dto);
        $searchEntityId = $dto->id;

        // add tags
        if ( $tags ) 
        {
            foreach ($tags as $tag)
            {
                $dto = new BOL_SearchEntityTag;
                $dto->entityTag = $tag;
                $dto->searchEntityId = $searchEntityId;
                $this->searchEntityTagDao->save($dto);
            }
        }
    }

    /**
     * Set entities status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param integer $status
     * @throws Exception
     * @return void
     */
    public function setEntitiesStatus( $entityType, $entityId, $status = self::ENTITY_STATUS_ACTIVE )
    {
        $this->searchEntityDao->setEntitiesStatus($entityType, $status, $entityId);
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
        $this->searchEntityDao->setEntitiesStatusByTags($tags, $status);
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
        // mark entity as deleted
        $this->searchEntityDao->
                setEntitiesStatus($entityType, BOL_SearchEntityDao::ENTITY_STATUS_DELETED, $entityId);
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
        // mark entities as deleted
        $this->searchEntityDao->
                setEntitiesStatus($entityType, BOL_SearchEntityDao::ENTITY_STATUS_DELETED);
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
        // mark entities as deleted
        $this->searchEntityDao->
                setEntitiesStatusByTags($tags, BOL_SearchEntityDao::ENTITY_STATUS_DELETED);
    }

    /**
     * Real delete entities
     *
     * @throws Exception
     * @return void
     */
    public function realDeleteEntities()
    {
        // get deleted entities
        if ( null != ($entities = $this->searchEntityDao->findDeletedEntities()) ) 
        {
            foreach ($entities as $entity)
            {
                // get tags list
                $tags = $this->searchEntityTagDao->findTags($entity->id);

                // delete assigned tags
                foreach ($tags as $tag) 
                {
                    $this->searchEntityTagDao->deleteById($tag->id);
                }

                // delete an entity part
                $this->searchEntityDao->deleteById($entity->id);
            }

            $this->searchEntityDao->optimizeTable();
            $this->searchEntityTagDao->optimizeTable();
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
        $this->searchEntityDao->changeActivationStatus($entityType, false);
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
        $this->searchEntityDao->changeActivationStatusByTags($tags, false);
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
        $this->searchEntityDao->changeActivationStatus($entityType);
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
        $this->searchEntityDao->changeActivationStatusByTags($tags);
    }

    /**
     * Search entities
     *
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param string $sort
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntities( $text, $first, $limit, 
            array $tags = array(), $sort = self::SORT_BY_RELEVANCE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        return $this->searchEntityDao->
                findEntitiesByText($text, $first, $limit, $tags, $sort, $sortDesc, $timeStart, $timeEnd);
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
    public function searchEntitiesCount( $text, array $tags = array(), $timeStart = 0,  $timeEnd = 0)
    {
        return $this->searchEntityDao->
                findEntitiesCountByText($text, $tags, $timeStart, $timeEnd);
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
    public function searchEntitiesCountByTags( array $tags, $timeStart = 0,  $timeEnd = 0)
    {
        return $this->searchEntityDao->findEntitiesCountByTags($tags, $timeStart, $timeEnd);
    }

    /**
     * Search entities by tags
     *
     * @param array $tags
     * @param integer $first
     * @param integer $limit
     * @param string $sort
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntitiesByTags(  array $tags, $first, $limit, 
            $sort = self::SORT_BY_DATE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        return $this->searchEntityDao->
                findEntitiesByTags($tags, $first, $limit, $sort, $sortDesc, $timeStart, $timeEnd);
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
    public function getAllEntities(  $first, $limit, $entityType = null )
    {
         if (null != ($entities = $this->
                 searchEntityDao->findAllEntities($first, $limit, $entityType)))  
         {
             // get entities' tags
            foreach ($entities as &$entity)
            {
                if (null != ($tags = $this->searchEntityTagDao->findTags($entity['id']))) 
                {
                    foreach ($tags as $tag)
                    {
                        $entity['tags'][] = $tag->entityTag;
                    }
                }
            }
         }

         return $entities;
    }
}