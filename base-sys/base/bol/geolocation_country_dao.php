<?php

class BOL_GeolocationCountryDao extends PEEP_BaseDao
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
     * @var BOL_GeolocationCountryDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_GeolocationCountryDao
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
        return 'BOL_GeolocationCountry';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        $prefix = ( defined('PEEP_DB_GEOLOCATION_PREFIX') ) ? PEEP_DB_GEOLOCATION_PREFIX : PEEP_DB_PREFIX;

        return $prefix . 'base_geolocation_country';
    }

    public function doesTableExist()
    {
        $sql = "show tables like '" . $this->getTableName() . "'";
        $result = $this->dbo->queryForColumn($sql);

        return $result !== null;
    }
}