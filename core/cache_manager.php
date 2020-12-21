<?php

class PEEP_CacheManager
{
    const CLEAN_ALL = 'all';
    const CLEAN_OLD = 'old';
    const CLEAN_MATCH_TAGS = 'match_tag';
    const CLEAN_MATCH_ANY_TAG = 'match_any_tag';
    const CLEAN_NOT_MATCH_TAGS = 'not_match_tags';
    const TAG_OPTION_INSTANT_LOAD = 'base.tag_option.instant_load';

    /**
     * @var PEEP_ICacheBackend
     */
    private $cacheBackend;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var boolean
     */
    private $cacheEnabled;

    /**
     * Singleton instance.
     *
     * @var PEEP_CacheManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_CacheManager
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
     * Constructor.
     */
    private function __construct()
    {
        $this->cacheEnabled = !false;
    }

    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled( $cacheEnabled )
    {
        $this->cacheEnabled = (bool) $cacheEnabled;
    }

    public function load( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->load($key);
        }

        return null;
    }

    public function test( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->test($key);
        }

        return false;
    }

    public function save( $data, $key, $tags = array(), $specificLifetime = false )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->save($data, $key, $tags, ($specificLifetime === false ? $this->lifetime : $specificLifetime));
        }

        return false;
    }

    public function remove( $key )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->remove($key);
        }

        return false;
    }

    public function clean( $tags = array(), $mode = self::CLEAN_MATCH_ANY_TAG )
    {
        if ( $this->cacheAvailable() )
        {
            return $this->cacheBackend->clean($tags, $mode);
        }

        return false;
    }

    /**
     * @param PEEP_ICacheBackend $cacheBackend
     */
    public function setCacheBackend( PEEP_ICacheBackend $cacheBackend )
    {
        $this->cacheBackend = $cacheBackend;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime( $lifetime )
    {
        $this->lifetime = $lifetime;
    }

    private function cacheAvailable()
    {
        return $this->cacheBackend !== null && $this->cacheEnabled;
    }
}