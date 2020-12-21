<?php

class BOL_ComponentPlaceDao extends PEEP_BaseDao
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
     * @var BOL_ComponentPlaceDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentPlaceDao
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
        return 'BOL_ComponentPlace';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_component_place';
    }

    public function cloneComponent( $uniqName )
    {
        $component = $this->findByUniqName($uniqName);
        $component->id = 0;
        $component->clone = 1;
        $component->uniqName = uniqid('admin-');
        $this->save($component);

        return $component;
    }

    public function findByUniqName( $uniqName )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uniqName', $uniqName);

        return $this->findObjectByExample($example);
    }

    public function deleteByUniqName( $uniqName )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('uniqName', $uniqName);

        return $this->deleteByExample($example);
    }

    public function deleteByComponentId( $componentId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('componentId', $componentId);

        return $this->deleteByExample($example);
    }

    public function findListByComponentId( $componentId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('componentId', $componentId);

        return $this->findListByExample($example);
    }

    public function findComponentList( $placeId )
    {
        $componentDao = BOL_ComponentDao::getInstance();

        $query =
            'SELECT `c`.*, `cp`.`id`, `cp`.`componentId`, `cp`.`clone`, `cp`.`uniqName` FROM `' . $this->getTableName() . '` AS `cp`
    			INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
    				WHERE `cp`.`placeId`=?';

        return $this->dbo->queryForList($query, array($placeId));
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

    public function findListBySection( $placeId, $section )
    {
        $componentDao = BOL_ComponentDao::getInstance();
        $componentSectionDao = BOL_ComponentPositionDao::getInstance();

        $query = '
            SELECT `c`.*, `cp`.`id`, `cp`.`componentId`, `cp`.`clone`, `cp`.`uniqName`, `p`.`order`  FROM `' . $this->getTableName() . '` AS `cp`
                INNER JOIN `' . $componentDao->getTableName() . '` AS `c` ON `cp`.`componentId` = `c`.`id`
                INNER JOIN `' . $componentSectionDao->getTableName() . '` AS `p` 
                    ON `p`.`componentPlaceUniqName` = `cp`.`uniqName`
                    WHERE `cp`.`placeId`=? AND `p`.`section`=?
        ';

        return $this->dbo->queryForList($query, array($placeId, $section));
    }
}