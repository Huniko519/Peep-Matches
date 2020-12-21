<?php

class PHOTO_BOL_PhotoCacheDao extends PEEP_BaseDao
{
    CONST KEY = 'key';
    CONST CREATE_TIMESTAMP = 'createTimestamp';
    
    CONST CACHE_LIFETIME = 10;
    
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
        return PEEP_DB_PREFIX . 'photo_cache';
    }
    
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_PhotoCache';
    }
    
    public function getKey( $searchVal )
    {
        return crc32(PEEP::getUser()->getId() . $searchVal);
    }
    
    public function getKeyAll( $searchVal )
    {
        return crc32(PEEP::getUser()->getId() . $searchVal . 'all');
    }

    public function findCacheByKey( $key )
    {
        if ( empty($key) )
        {
            return NULL;
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::KEY . '` = :key
            LIMIT 1';
        
        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array('key' => $key));
    }
    
    public function cleareCache()
    {
        return $this->dbo->query('DELETE FROM `' . $this->getTableName() . '`
            WHERE `' . self::CREATE_TIMESTAMP . '` <= :time', array('time' => time() - self::CACHE_LIFETIME * 60));
    }
}
