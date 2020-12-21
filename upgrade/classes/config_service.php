<?php

final class UPDATE_ConfigService
{
    /**
     * @var PEEP_Config
     */
    private $configManager;
    /**
     * @var UPDATE_ConfigService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UPDATE_ConfigService
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
        $this->configManager = PEEP_Config::getInstance();
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
        return $this->configManager->getValue($key, $name);
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
        $this->configManager->addConfig($key, $name, $value, $descripton);
    }

    /**
     * Deletes config by provided plugin key and config name.
     *
     * @param string $key
     * @param string $name
     */
    public function deleteConfig( $key, $name )
    {
        $this->configManager->deleteConfig($key, $name);
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
        return $this->configManager->configExists($key, $name);
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
        $this->configManager->saveConfig($key, $name, $value);
    }
}