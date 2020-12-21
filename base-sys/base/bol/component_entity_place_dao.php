<?php

class BOL_ComponentEntityPlaceDao extends PEEP_BaseDao
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
     * @var BOL_ComponentEntityPlaceDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentEntityPlaceDao
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
        return 'BOL_ComponentEntityPlace';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_component_entity_place';
    }

    public function findByUniqName( $uniqName, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uniqName', $uniqName);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    public function deleteByUniqName( $uniqName, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uniqName', $uniqName);
        $example->andFieldEqual('entityId', $entityId);

        return $this->deleteByExample($example);
    }

    public function deleteList( $placeId, $entityId )
    {
        $entityId = (int) $entityId;
        if ( !$entityId )
        {
            throw new InvalidArgumentException('Invalid argument $entityId');
        }

        $placeId = (int) $placeId;
        if ( !$placeId )
        {
            throw new InvalidArgumentException('Invalid argument $placeId');
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('placeId', $placeId);

        return $this->deleteByExample($example);
    }

    public function findAdminComponentList( $placeId, $entityId )
    {
        $placeDao = BOL_ComponentPlaceDao::getInstance();
        $query =
            "SELECT `up`.* FROM `" . $this->getTableName() . "` AS `up` 
                 LEFT JOIN `" . $placeDao->getTableName() . "` AS `p` ON `p`.`uniqName`=`up`.`uniqName`
                    WHERE `p`.`uniqName` IS NOT NULL AND `up`.`placeId`=? AND `up`.`entityId`=?";

        return $this->dbo->queryForList($query, array($placeId, $entityId));
    }

    public function findAdminComponentIdList( $placeId, $entityId )
    {
        $dtoList = $this->findAdminComponentList($placeId, $entityId);
        $idList = array();
        foreach ( $dtoList as $dto )
        {
            $idList[] = $dto['id'];
        }

        return $idList;
    }

    public function deleteAllByUniqName( $uniqName )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uniqName', $uniqName);

        return $this->deleteByExample($example);
    }

    public function findComponentList( $placeId, $entityId )
    {
        $componentDao = BOL_ComponentDao::getInstance();

        $query =
            'SELECT `c`.*, `cp`.`id`, `cp`.`componentId`, `cp`.`clone`, `cp`.`uniqName` FROM `' . $this->getTableName() . '` AS `cp`
    			INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
    				WHERE `cp`.`placeId`=? AND `cp`.`entityId`=?';

        return $this->dbo->queryForList($query, array($placeId, $entityId));
    }

    public function findComponentListByIdList( array $componentIds )
    {
        $componentDao = BOL_ComponentDao::getInstance();

        $query = '
    		SELECT `c`.*, cp.`id`  FROM `' . $this->getTableName() . '` AS `cp`
    			INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
    				WHERE `cp`.`componentId` IN (' . implode(', ', $componentIds) . ')
    	';

        return $this->dbo->queryForColumnList($query, array($placeId));
    }
}