<?php

class PHOTO_BOL_SearchEntityDao extends PEEP_BaseDao
{
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
        return PEEP_DB_PREFIX . 'photo_search_entity';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_SearchEntity';
    }
}
