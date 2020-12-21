<?php

final class PEEP_PluginManager
{
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * List of active plugins.
     *
     * @var array
     */
    private $activePlugins;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->pluginService = BOL_PluginService::getInstance();
        $this->readPluginsList();
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_PluginManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_PluginManager
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
     * Returns active plugin object.
     *
     * @param string $key
     * @return PEEP_Plugin
     */
    public function getPlugin( $key )
    {
        if ( !array_key_exists(mb_strtolower(trim($key)), $this->activePlugins) )
        {
            throw new InvalidArgumentException("There is no active plugin with key `" . $key . "`");
        }

        return $this->activePlugins[mb_strtolower(trim($key))];
    }

    public function initPlugins()
    {
        /* @var $value PEEP_Plugin */
        foreach ( $this->activePlugins as $value )
        {
            $this->initPlugin($value);
        }
    }

    public function initPlugin( PEEP_Plugin $pluginObject )
    {
        $this->addPackagePointers($pluginObject->getDto());

        $initDirPath = $pluginObject->getRootDir();

        if ( PEEP::getApplication()->getContext() == PEEP::CONTEXT_MOBILE )
        {
            $initDirPath = $pluginObject->getMobileDir();
        }
        else if ( PEEP::getApplication()->getContext() == PEEP::CONTEXT_API )
        {
            $initDirPath = $pluginObject->getApiDir();
        }

        if ( file_exists($initDirPath . 'init.php') )
        {
            PEEP::getEventManager()->trigger(new PEEP_Event("core.performance_test", array("key" => "plugin_init.start", "pluginKey" => $pluginObject->getKey())));
            include $initDirPath . 'init.php';
            PEEP::getEventManager()->trigger(new PEEP_Event("core.performance_test", array("key" => "plugin_init.end", "pluginKey" => $pluginObject->getKey())));
        }
    }

    public function addPackagePointers( BOL_Plugin $pluginDto )
    {
        $plugin = $this->pluginService->getPluginObject($pluginDto);
        $upperedKey = mb_strtoupper($plugin->getKey());
        $autoloader = PEEP::getAutoloader();

        $autoloader->addPackagePointer($upperedKey . '_CMP', $plugin->getCmpDir());
        $autoloader->addPackagePointer($upperedKey . '_CTRL', $plugin->getCtrlDir());
        $autoloader->addPackagePointer($upperedKey . '_BOL', $plugin->getBolDir());
        $autoloader->addPackagePointer($upperedKey . '_CLASS', $plugin->getClassesDir());
        $autoloader->addPackagePointer($upperedKey . '_MCMP', $plugin->getMobileCmpDir());
        $autoloader->addPackagePointer($upperedKey . '_MCTRL', $plugin->getMobileCtrlDir());
        $autoloader->addPackagePointer($upperedKey . '_MBOL', $plugin->getMobileBolDir());
        $autoloader->addPackagePointer($upperedKey . '_MCLASS', $plugin->getMobileClassesDir());
        $autoloader->addPackagePointer($upperedKey . '_ACTRL', $plugin->getApiCtrlDir());
        $autoloader->addPackagePointer($upperedKey . '_ABOL', $plugin->getApiBolDir());
        $autoloader->addPackagePointer($upperedKey . '_ACLASS', $plugin->getApiClassesDir());
    }

    /**
     * Update active plugins list for manager.
     */
    public function readPluginsList()
    {
        $this->activePlugins = array();

        /* read all plugins from DB */
        $plugins = $this->pluginService->findActivePlugins();

        usort($plugins, array(__CLASS__, 'sortPlugins'));

        /* @var $value BOL_Plugin */
        foreach ( $plugins as $value )
        {
            $this->activePlugins[$value->getKey()] = $this->pluginService->getPluginObject($value);
        }
    }

    public static function sortPlugins( BOL_Plugin $a, BOL_Plugin $b )
    {
        if ( $a->getId() == $b->getId() )
        {
            return 0;
        }

        return $a->getId() > $b->getId();
    }

    /**
     * Returns plugin key for provided module name.
     *
     * @param string $moduleName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getPluginKey( $moduleName )
    {
        foreach ( $this->activePlugins as $key => $value )
        {
            if ( $moduleName === $value->getModuleName() )
            {
                return $key;
            }
        }

        throw new InvalidArgumentException('There is no plugin with module name `' . $moduleName . '` !');
    }

    /**
     * Returns module name for provided plugin key.
     *
     * @param string $pluginKey
     * @return string
     * @throws InvalidArgumentException
     */
    public function getModuleName( $pluginKey )
    {
        if ( !array_key_exists($pluginKey, $this->activePlugins) )
        {
            throw new InvalidArgumentException("There is no active plugin with key `" . $key . "`");
        }

        return $this->activePlugins[$pluginKey]->getModuleName();
    }

    /**
     * Checks if plugin is active.
     *
     * @param string $pluginKey
     * @return boolean
     */
    public function isPluginActive( $pluginKey )
    {
        return array_key_exists($pluginKey, $this->activePlugins);
    }

    /**
     * Adds admin settings page route.
     *
     * @param string $pluginKey
     * @param string $routeName
     */
    public function addPluginSettingsRouteName( $pluginKey, $routeName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($pluginKey));

        if ( $plugin !== null )
        {
            $plugin->setAdminSettingsRoute($routeName);
            $this->pluginService->savePlugin($plugin);
        }
    }

    /**
     * Adds spec. uninstall page route name.
     *
     * @param string $key
     * @param string $routName
     */
    public function addUninstallRouteName( $key, $routName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($key));

        if ( $plugin !== null )
        {
            $plugin->setUninstallRoute($routName);
            $this->pluginService->savePlugin($plugin);
        }
    }
}
