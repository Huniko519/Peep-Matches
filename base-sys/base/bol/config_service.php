<?php

final class BOL_ConfigService
{
    /**
     * @var BOL_ConfigDao
     */
    private $configDao;
    /**
     * @var BOL_ConfigService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ConfigService
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
        $this->configDao = BOL_ConfigDao::getInstance();
    }

    /**
     * Returns config value for provided key and name.
     *
     * @param string $key
     * @param string $name
     * @return string
     */
    public function findConfigValue( $key, $name )
    {
        $config = $this->configDao->findConfig($key, $name);

        if ( $config === null )
        {
            return null;
        }

        return $config->getValue();
    }

    /**
     * Returns config item for provided key and name.
     * 
     * @param $key
     * @param $name
     * @return unknown_type
     */
    public function findConfig( $key, $name )
    {
        return $this->configDao->findConfig($key, $name);
    }

    /**
     * Returns config items list for provided plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function findConfigsList( $key )
    {
        return $this->configDao->findConfigsList($key);
    }

    /**
     * Returns all configs.
     *
     * @return array<BOL_Config>
     */
    public function findAllConfigs()
    {
        return $this->configDao->findAll();
    }

    /**
     * Adds new config item.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function addConfig( $key, $name, $value, $description = null )
    {
        if ( $this->findConfig($key, $name) !== null )
        {
            throw new InvalidArgumentException("Can't add config `" . $name . "` in section `" . $key . "`. Duplicated key and name!");
        }

        $newConfig = new BOL_Config();
        $newConfig->setKey($key)->setName($name)->setValue($value)->setDescription($description);
        $this->configDao->save($newConfig);
    }

    /**
     * Updates config item value.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function saveConfig( $key, $name, $value )
    {
        $config = $this->configDao->findConfig($key, $name);

        if ( $config === null )
        {
            throw new InvalidArgumentException("Can't find config `" . $name . "` in section `" . $key . "`!");
        }

        $this->configDao->save($config->setValue($value));
    }

    /**
     * Removes config item by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function removeConfig( $key, $name )
    {
        $this->configDao->removeConfig($key, $name);
    }

    /**
     * Removes all plugin configs.
     * 
     * @param string $pluginKey
     */
    public function removePluginConfigs( $pluginKey )
    {
        $this->configDao->removeConfigs($pluginKey);
    }
}