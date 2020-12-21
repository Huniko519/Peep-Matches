<?php

class BOL_FlagDao extends PEEP_BaseDao
{
    /**
     *
     * @var BOL_FlagDao
     */
    private static $classInstance;

    /**
     * Enter description here...
     *
     * @return BOL_FlagDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_flag';
    }

    public function getDtoClassName()
    {
        return 'BOL_Flag';
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
        $example = new PEEP_Example();

        $example->andFieldEqual('entityType', $entityType)
            ->andFieldEqual('entityId', $entityId)
            ->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findByEntityTypeList( $entityTypes, array $limit = null )
    {
        $example = new PEEP_Example();
        $example->andFieldInArray("entityType", $entityTypes);
        
        if ( !empty($limit) )
        {
            $example->setLimitClause($limit[0], $limit[1]);
        }
        
        $example->setOrder("timeStamp DESC");
        
        return $this->findListByExample($example);
    }

    /**
     * 
     * @param string $entityType
     * @return int
     */
    public function countByEntityType( $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual("entityType", $entityType);

        return $this->countByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findCountForEntityTypeList( $entityTypes )
    {
        if ( empty($entityTypes) )
        {
            return array();
        }
        
        $query = "SELECT count(DISTINCT `entityId`) `count`, `entityType` "
                    . "FROM `" . $this->getTableName() . "` "
                    . "WHERE `entityType` IN ('" . implode("', '", $entityTypes) . "') "
                    . "GROUP BY `entityType`";
        
        $out = array();
        foreach ( $this->dbo->queryForList($query) as $row )
        {
            $out[$row['entityType']] = $row['count'];
        }
        
        return $out;
    }
    
    public function deleteFlagList( $entityType, array $entityIdList = null )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityType', $entityType);
        
        if ( !empty($entityIdList) )
        {
            $example->andFieldInArray("entityId", $entityIdList);
        }

        $this->deleteByExample($example);
    }
    
    public function deleteEntityFlags( $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }
}