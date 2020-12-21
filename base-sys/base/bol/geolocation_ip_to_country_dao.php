<?php

class BOL_GeolocationIpToCountryDao extends PEEP_BaseDao
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
     * @var BOL_GeolocationIpToCountryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_GeolocationIpToCountryDao
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
        return 'BOL_GeolocationIpToCountry';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        $prefix = ( defined('PEEP_DB_GEOLOCATION_PREFIX') ) ? PEEP_DB_GEOLOCATION_PREFIX : PEEP_DB_PREFIX;

        return $prefix . 'base_geolocation_ip_to_country';
    }
    private $cachedItems = array();

    public function ipToCountryCode3( $ip )
    {
        if ( !array_key_exists($ip, $this->cachedItems) )
        {
            $sql = 'SELECT `cc3` FROM `' . $this->getTableName() . '` WHERE inet_aton(:ip) >= ipFrom AND inet_aton(:ip) <= ipTo';

            $this->cachedItems[$ip] = $this->dbo->queryForColumn($sql, array('ip' => $ip));
        }

        return empty($this->cachedItems[$ip]) ? null : $this->cachedItems[$ip];
    }
}