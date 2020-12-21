<?php

interface PEEP_CacheService
{

    /**
     * Returns stored data if an item with such key exists on the cache at this moment. 
     * 
     * @param string $key
     * @return mixed
     */
    public function get( $key );

    /**
     * Stores an item var with key on the cache. 
     * Parameter lifeTime is expiration time in seconds.
     * 
     * @param string $key
     * @param mixed $var
     * @param int $lifeTime
     * @return mixed
     */
    public function set( $key, $var, $lifeTime = 0 );
}