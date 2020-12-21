<?php

class BOL_GeolocationService
{
    private static $classInstance;
    /**
     * @var boolean
     */
    private $isAvailable;
    /**
     * @var BOL_GeolocationCountryDao
     */
    private $countryDao;
    /**
     * @var BOL_GeolocationIpToCountryDao
     */
    private $ipCountryDao;

    /**
     *
     * @return BOL_GeolocationService 
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->countryDao = BOL_GeolocationCountryDao::getInstance();
        $this->ipCountryDao = BOL_GeolocationIpToCountryDao::getInstance();
        
        $this->isAvailable = $this->countryDao->doesTableExist();
    }

    public function ipToCountryCode3( $ip )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        return $this->ipCountryDao->ipToCountryCode3($ip);
    }

    public function getCountryNameListForCC3( array $codes )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countries = array();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $codes as $code )
        {
            $countries[$code] = $this->getCountryNameForCC3($code);
        }

        return $countries;
    }

    public function getAllCountryNameListForCC3()
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countryList = $this->countryDao->findAll();
        $countries = array();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $countryList as $country )
        {
            $countries[$country->cc3] = $this->getCountryNameForCC3($country->cc3);
        }

        return $countries;
    }

    public function getCountryNameForCC3( $code )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        return PEEP::getLanguage()->text('base', 'geolocation_country_name_' . $code);
    }

    public function isServiceAvailable()
    {
        return $this->isAvailable;
    }

    public function updateCountryNameListToLanguage( $languageId )
    {
        if ( !$this->isServiceAvailable() )
        {
            return;
        }

        $countryList = $this->countryDao->findAll();

        /* @var $country BOL_GeolocationCountry */
        foreach ( $countryList as $country )
        {
            $key = BOL_LanguageService::getInstance()->findKey('base', 'geolocation_country_name_' . $country->cc3);
            if ( $key !== null )
            {
                $value = BOL_LanguageService::getInstance()->findValue($languageId, $key->id);
                if ( $value !== null )
                {
                    $value->value = ucwords(strtolower($country->name));
                    BOL_LanguageService::getInstance()->saveValue($value, false);
                }
            }
        }
    }
    // "LOAD DATA LOCAL INFILE '/home/nurlan/Downloads/ip-to-country.csv' INTO TABLE `peep_base_geolocation_ip2country` FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' (`ipFrom`, `ipTo`, `cc2`, `cc3`, `name`)";
    //insert into peep_base_geolocation_country (`cc2`, `cc3`, `name`) select `cc2`, `cc3`, `name` from peep_base_geolocation_ip2country group by `cc3`
}