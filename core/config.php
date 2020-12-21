<?php

class PEEP_Config
{
    /**
     * @var BOL_ConfigService
     */
    private $configService;
    /**
     * @var array
     */
    private $cachedConfigs;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->configService = BOL_ConfigService::getInstance();

        $this->generateCache();
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Config
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Config
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function generateCache()
    {
        $configs = $this->configService->findAllConfigs();

        /* @var $config BOL_Config */
        foreach ( $configs as $config )
        {
            if ( !isset($this->cachedConfigs[$config->getKey()]) )
            {
                $this->cachedConfigs[$config->getKey()] = array();
            }

            $this->cachedConfigs[$config->getKey()][$config->getName()] = $config->getValue();
        }
    }

    /**
     * Returns config value for provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     * @return string|null
     */
    public function getValue( $key, $name )
    {
        return ( isset($this->cachedConfigs[$key][$name]) ) ? $this->cachedConfigs[$key][$name] : null;
    }

    /**
     * Returns all config values for plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function getValues( $key )
    {
        return ( isset($this->cachedConfigs[$key]) ) ? $this->cachedConfigs[$key] : array();
    }

    /**
     * Adds plugin config.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function addConfig( $key, $name, $value, $descripton = null )
    {
        $this->configService->addConfig($key, $name, $value, $descripton);
        $this->generateCache();
    }

    /**
     * Deletes config by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function deleteConfig( $key, $name )
    {
        $this->configService->removeConfig($key, $name);
        $this->generateCache();
    }

    /**
     * Removes all plugin configs.
     * 
     * @param string $key
     */
    public function deletePluginConfigs( $key )
    {
        $this->configService->removePluginConfigs($key);
    }

    /**
     * Checks if config exists.
     *
     * @param string $key
     * @param string $name
     * @return boolean
     */
    public function configExists( $key, $name )
    {
        return array_key_exists($key, $this->cachedConfigs) && array_key_exists($name, $this->cachedConfigs[$key]);
    }

    /**
     * Updates config value.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function saveConfig( $key, $name, $value )
    {
        $this->configService->saveConfig($key, $name, $value);
        $this->generateCache();
    }
}