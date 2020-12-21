<?php

class GOOGLELOCATION_BOL_LocationDao extends PEEP_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const COUNTRY_CODE = 'countryCode';
    const ADDRESS_STRING = 'address';
    const LATITUDE = 'lat';
    const LONDITUDE = 'lng';
    
    const ENTITY_TYPE_USER = 'user';
    const ENTITY_TYPE_EVENT = 'event';

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var GOOGLELOCATION_BOL_LocationDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GOOGLELOCATION_BOL_LocationDao
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
        return 'GOOGLELOCATION_BOL_Location';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'googlelocation_data';
    }
    
    /**
     * @param integer $userId
     * @return GOOGLELOCATION_BOL_Location
     */
    public function findByUserId( $userId )
    {
        if ( empty($userId) )
        {
            return null;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, (int)$userId);
        $example->andFieldEqual(self::ENTITY_TYPE, self::ENTITY_TYPE_USER);
        return $this->findObjectByExample($example);
    }
    
    
    /**
     * @param array $userIdList
     * @return array <GOOGLELOCATION_BOL_Location>
     */
    public function findByUserIdList( array $userIdList )
    {
        if ( empty($userIdList) )
        {
            return array();
        }
        
        $example = new PEEP_Example();
        $example->andFieldInArray(self::ENTITY_ID, $userIdList);
        $example->andFieldEqual(self::ENTITY_TYPE, self::ENTITY_TYPE_USER);
        return $this->findListByExample($example);
    }


    /**
     * @param string $prefix
     * @param float $southWestLat
     * @param float $southWestLng
     * @param float $northEastLat
     * @param float $northEastLng
     * @param string $countryCode
     * @return string
     */
    
    public function getSearchInnerJoinSql( $prefix, $southWestLat, $southWestLng, $northEastLat, $northEastLng, $countryCode = null, $joinType = 'INNER' )
    {
        $countryStr = "";
        /* if ( !empty($countryCode) )
        {
            $countryStr =" AND location.".self::COUNTRY_CODE." = '".$this->dbo->escapeString($countryCode)."' ";
        } */

        $sql = $joinType . " JOIN ".$this->getTableName()." location ON ( $prefix.id = location.entityId AND location.entityType = '".self::ENTITY_TYPE_USER."'
                 AND (
                         location.southWestLat >= ".(float)$southWestLat."
                         AND location.southWestLat <= ".(float)$northEastLat."
                             OR
                         location.northEastLat >= ".(float)$southWestLat."
                         AND location.northEastLat <= ".(float)$northEastLat."
                             OR
                         location.southWestLat >= ".(float)$southWestLat."
                         AND location.northEastLat <= ".(float)$northEastLat."
                             OR
                         location.southWestLat <= ".(float)$southWestLat."
                         AND location.northEastLat >= ".(float)$northEastLat."
                     )

                 AND (
                         location.southWestLng >= ".(float)$southWestLng."
                         AND location.southWestLng <= ".(float)$northEastLng."
                             OR
                         location.northEastLng >= ".(float)$southWestLng."
                         AND location.northEastLng <= ".(float)$northEastLng."
                             OR
                         location.southWestLng >= ".(float)$southWestLng."
                         AND location.northEastLng <= ".(float)$northEastLng."
                             OR
                         location.southWestLng <= ".(float)$southWestLng."
                         AND location.northEastLng >= ".(float)$northEastLng."
                     ) $countryStr ) ";

        return $sql;
    }
    
    /**
     * @return array
     */
    public function getAllLocationsForUserMap()
    {
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "GOOGLELOCATION_BOL_LocationDao::getAllLocationsForUserMap"
        ));

        $query = "SELECT l.* FROM " . $this->getTableName() . " l

                INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
                    ON( `l`.`entityId` = `u`.`id` )

               " . $queryParts["join"] . "

                WHERE " . $queryParts["where"] . " AND l.entityType = '".self::ENTITY_TYPE_USER."' ";

        return $this->dbo->queryForList($query);
    }
    
    public function getAllLocationsForEntityType($entityType)
    {
        $query = "SELECT l.* FROM " . $this->getTableName() . " l
                WHERE l.entityType = :entityType ";
                
        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }
    
    public function findUserListByCoordinates( $lat, $lng, $first, $count, $userIdList = array() )
    {
        if ( !isset($lat) || !isset($lng) )
        {
            return array();
        }

        $where = "";

        if ( !empty($userIdList) && is_array($userIdList) )
        {
            $where = " AND u.id IN ( ". $this->dbo->mergeInClause($userIdList) ." ) ";
        }

        

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "GOOGLELOCATION_BOL_LocationDao::findUserListByCoordinates"
        ));

        $query = "SELECT u.* FROM " . $this->getTableName() . " l

                INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
                    ON( `l`.`entityId` = `u`.`id` )

               " . $queryParts["join"] . "

                WHERE " . $queryParts["where"] . $where . " AND l.lat = :lat AND l.lng = :lng AND l.entityType = '".self::ENTITY_TYPE_USER."'  LIMIT :first, :count";
        
        return $this->dbo->queryForObjectList($query, BOL_UserDao::getInstance()->getDtoClassName(), array( 'lat' => $lat, 'lng' => $lng, 'first' => (int)$first, 'count' => (int)$count ));
    }
    
    public function findUserCountByCoordinates( $lat, $lng, $userIdList = array() )
    {
        if ( !isset($lat) || !isset($lng) )
        {
            return 0;
        }

        $where = "";

        if ( !empty($userIdList) && is_array($userIdList) )
        {
            $where = " AND u.id IN ( ". $this->dbo->mergeInClause($userIdList) ." ) ";
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "GOOGLELOCATION_BOL_LocationDao::findUserCountByCoordinates"
        ));

        $query = "SELECT Count(DISTINCT l.entityId) FROM " . $this->getTableName() . " l

                INNER JOIN `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
                    ON( `l`.`entityId` = `u`.`id` )

                " . $queryParts["join"] . "

                WHERE " . $queryParts["where"] . $where . " AND l.lat = :lat AND l.lng = :lng AND l.entityType = '".self::ENTITY_TYPE_USER."' ";

        return $this->dbo->queryForColumn($query, array( 'lat' => $lat, 'lng' => $lng ));
    }
    
    public function getLocationName( $lat, $lng )
    {
        if ( !isset($lat) || !isset($lng) )
        {
            return null;
        }
        
        $query = "SELECT l.address FROM " . $this->getTableName() . " l
                WHERE l.lat = :lat AND l.lng = :lng LIMIT 1 ";
        
        return $this->dbo->queryForColumn($query, array( 'lat' => $lat, 'lng' => $lng ));
    }
    
    public function findEntityIdAndEntityType( $entityId, $entityType )
    {
        if ( empty($entityId) || empty($entityType) )
        {
            return null;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        return $this->findObjectByExample($example);
    }
    
    public function deleteByEntityType( $entityType )
    {
        if ( empty($entityType) )
        {
            return false;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        return $this->deleteByExample($example);
    }
    
    public function deleteByEntityIdAndEntityType( $entityId, $entityType )
    {
        if ( empty($entityId) || empty($entityType) )
        {
            return false;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        return $this->deleteByExample($example);
    }
    
    public function getListOrderedByDistance( $userIdList, $first, $count, $lat, $lon )
    {
        if ( empty($userIdList) )
        {
            return array();
        }
        
        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("user", "id", array(
            "method" => "GOOGLELOCATION_BOL_LocationDao::getListOrderedByDistance"
        ));
        
        $where = '';
        
        $join = ' INNER JOIN `'.GOOGLELOCATION_BOL_LocationDao::getInstance()->getTableName(). '` location ON ( user.id = location.entityId AND location.entityType = \''.GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER.'\' )';
            
        $sql = "SELECT `user`.* FROM `" . BOL_UserDao::getInstance()->getTableName() . "` `user`
            {$queryParts["join"]}
            LEFT JOIN `".GOOGLELOCATION_BOL_LocationDao::getInstance()->getTableName(). "` location ON ( user.id = location.entityId AND location.entityType = '".GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER."' )
            WHERE `user`.`id` IN (" . $this->dbo->mergeInClause($userIdList) . ") $where
            ORDER BY if( location.id IS NULL, 99999999999999, (
            acos (
                    cos ( radians(:lat) )
                    * cos( radians( location.lat ) )
                    * cos( radians( location.lng ) - radians(:lon) )
                    + sin ( radians(:lat) )
                    * sin( radians( location.lat ) )
                ) )
            ) ASC , `user`.`activityStamp` DESC limit :first, :count";
            
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('first' => $first, 'count' => $count, 'lat' => $lat, 'lon' => $lon) );
    }
}
