<?php

class PHOTO_BOL_SearchEntityTypeDao extends PEEP_BaseDao
{
    CONST ENTITY_TYPE = 'entityType';
    
    private static $classInstance;

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
        return PEEP_DB_PREFIX . 'photo_search_entity_type';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchEntityType';
    }
}
