<?php

class CNEWS_BOL_LikeDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_LikeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_LikeDao
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
        return 'CNEWS_BOL_Like';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_like';
    }

    public function addLike( $userId, $entityType, $entityId )
    {
        $dto = $this->findLike($userId, $entityType, $entityId);

        if ( $dto !== null )
        {
            return $dto;
        }

        $dto = new CNEWS_BOL_Like();
        $dto->entityType = $entityType;
        $dto->entityId = $entityId;
        $dto->userId = $userId;
        $dto->timeStamp = time();

        $this->save($dto);

        return $dto;
    }

    public function findLike( $userId, $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->findObjectByExample($example);
    }

    public function removeLike( $userId, $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->deleteByExample($example);
    }

    public function removeLikesByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    public function deleteByEntity( $entityType, $entityId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->deleteByExample($example);
    }

    public function findByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    public function findByEntity( $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findListByExample($example);
    }

    public function findByEntityList( $entityList )
    {
        if ( empty($entityList) )
        {
            return array();
        }

        $entityListCondition = array();

        foreach ( $entityList as $entity )
        {
            $entityListCondition[] = 'entityType="' . $entity['entityType'] . '" AND entityId="' . $entity['entityId'] . '"';
        }

        $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' OR ', $entityListCondition);

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }



    public function findCountByEntity( $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->countByExample($example);
    }
}