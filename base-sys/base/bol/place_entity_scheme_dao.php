<?php

class BOL_PlaceEntitySchemeDao extends PEEP_BaseDao
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
     * @var BOL_ComponentSettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentSettingDao
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
        return 'BOL_PlaceEntityScheme';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_place_entity_scheme';
    }

    /**
     *
     * @param int $placeId
     * @param int $entityId
     * @return BOL_PlaceScheme
     */
    public function findPlaceScheme( $placeId, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('placeId', $placeId);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }
}