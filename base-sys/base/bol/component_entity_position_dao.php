<?php

class BOL_ComponentEntityPositionDao extends PEEP_BaseDao
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
     * @var BOL_ComponentEntitySectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentEntityPositionDao
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
        return 'BOL_ComponentEntityPosition';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_component_entity_position';
    }

    public function findAllPositionList( $placeId, $entityId )
    {
        $componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $componentEntityPlaceDao = BOL_ComponentEntityPlaceDao::getInstance();

        $query = '
        	SELECT `p`.* FROM `' . $this->getTableName() . '` AS `p`
        	INNER JOIN ( 
            	( SELECT `uniqName` FROM `' . $componentPlaceDao->getTableName() . '`
            		WHERE `placeId`=:placeId )
            	UNION
            	( SELECT `uniqName` FROM `' . $componentEntityPlaceDao->getTableName() . '`
            		WHERE `placeId`=:placeId AND `entityId`=:entityId )
        	) AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName` AND `p`.`entityId`=:entityId
        ';

        return $this->dbo->queryForObjectList($query,
            $this->getDtoClassName(),
            array('placeId' => $placeId, 'entityId' => $entityId));
    }

    public function findAllPositionIdList( $placeId, $entityId )
    {
        $positionDtoList = $this->findAllPositionList($placeId, $entityId);

        $idList = array();
        foreach ( $positionDtoList as $item )
        {
            $idList[] = $item->id;
        }

        return $idList;
    }

    public function findSectionPositionIdList( $placeId, $entityId, $section )
    {
        $componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $componentEntityPlaceDao = BOL_ComponentEntityPlaceDao::getInstance();

        $query = '
        	SELECT `p`.`id` FROM `' . $this->getTableName() . '` AS `p`
        	INNER JOIN ( 
            	( SELECT `uniqName` FROM `' . $componentPlaceDao->getTableName() . '`
            		WHERE `placeId`=:placeId )
            	UNION
            	( SELECT `uniqName` FROM `' . $componentEntityPlaceDao->getTableName() . '`
            		WHERE `placeId`=:placeId AND `entityId`=:entityId )
        	) AS `c` ON `p`.`componentPlaceUniqName` = `c`.`uniqName` AND `section`=:section AND `p`.`entityId`=:entityId
        ';

        return $this->dbo->queryForColumnList($query,
            array('placeId' => $placeId, 'entityId' => $entityId, 'section' => $section));
    }

    public function deleteAllByUniqName( $uniqName )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('componentPlaceUniqName', $uniqName);

        return $this->deleteByExample($example);
    }

    public function deleteByUniqNameList( $entityId, $uniqNameList = array() )
    {
        $entityId = (int) $entityId;
        if ( !$entityId )
        {
            throw new InvalidArgumentException('Invalid argument $entityId');
        }

        if ( empty($uniqNameList) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldInArray('componentPlaceUniqName', $uniqNameList);

        return $this->deleteByExample($example);
    }
}