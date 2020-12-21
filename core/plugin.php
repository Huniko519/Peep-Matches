<?php

class PEEP_Plugin
{
    /**
     * Plugin dir/module name.
     *
     * @var string
     */
    protected $dirName;
    /**
     * Plugin unique key.
     *
     * @var string
     */
    protected $key;
    /**
     * @var boolean
     */
    protected $active;
    /**
     * @var BOL_Plugin
     */
    protected $dto;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( $params )
    {
        if ( isset($params['dir_name']) )
        {
            $this->dirName = trim($params['dir_name']);
        }

        if ( isset($params['key']) )
        {
            $this->key = trim($params['key']);
        }

        if ( isset($params['active']) )
        {
            $this->active = (bool) $params['active'];
        }

        if ( isset($params['dto']) )
        {
            $this->dto = $params['dto'];
        }
    }

    /**
     * Returns plugin dir/module name.
     *
     * @return string
     */
    public function getDirName()
    {
        return $this->dirName;
    }

    /**
     * Returns plugin unique key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Checks if plugin is active.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Returns plugin data transfer object.
     *
     * @return BOL_Plugin
     */
    public function getDto()
    {
        return $this->dto;
    }

    public function getUserFilesDir()
    {
        return PEEP_DIR_PLUGIN_USERFILES . $this->getDirName() . DS;
    }

    public function getUserFilesUrl()
    {
        return PEEP_URL_PLUGIN_USERFILES . $this->getDirName() . '/';
    }

    public function getPluginFilesDir()
    {
        return PEEP_DIR_PLUGINFILES . $this->getDirName() . DS;
    }

    public function getRootDir()
    {
        return PEEP_DIR_PLUGIN . $this->dirName . DS;
    }

    public function getMobileDir()
    {
        return $this->getRootDir() . 'mobile' . DS;
    }

    public function getCmpDir()
    {
        return $this->getRootDir() . 'components' . DS;
    }

    public function getMobileCmpDir()
    {
        return $this->getMobileDir() . 'components' . DS;
    }

    public function getViewDir()
    {
        return $this->getRootDir() . 'views' . DS;
    }

    public function getMobileViewDir()
    {
        return $this->getMobileDir() . 'views' . DS;
    }

    public function getCmpViewDir()
    {
        return $this->getViewDir() . 'components' . DS;
    }

    public function getMobileCmpViewDir()
    {
        return $this->getMobileViewDir() . 'components' . DS;
    }

    public function getCtrlViewDir()
    {
        return $this->getViewDir() . 'controllers' . DS;
    }

    public function getMobileCtrlViewDir()
    {
        return $this->getMobileViewDir() . 'controllers' . DS;
    }

    public function getCtrlDir()
    {
        return $this->getRootDir() . 'controllers' . DS;
    }

    public function getMobileCtrlDir()
    {
        return $this->getMobileDir() . 'controllers' . DS;
    }

    public function getDecoratorDir()
    {
        return $this->getRootDir() . 'decorators' . DS;
    }

    public function getMobileDecoratorDir()
    {
        return $this->getMobileDir() . 'decorators' . DS;
    }

    public function getStaticDir()
    {
        return $this->getRootDir() . 'static' . DS;
    }

    public function getBolDir()
    {
        return $this->getRootDir() . 'bol' . DS;
    }

    public function getMobileBolDir()
    {
        return $this->getMobileDir() . 'bol' . DS;
    }

    public function getClassesDir()
    {
        return $this->getRootDir() . 'classes' . DS;
    }

    public function getMobileClassesDir()
    {
        return $this->getMobileDir() . 'classes' . DS;
    }

    public function getStaticJsDir()
    {
        return $this->getStaticDir() . 'js' . DS;
    }

    public function getModuleName()
    {
        return $this->dirName;
    }

    public function getStaticUrl()
    {
        return PEEP_URL_STATIC_PLUGINS . $this->getModuleName() . '/';
    }

    public function getStaticJsUrl()
    {
        return $this->getStaticUrl() . 'js/';
    }

    public function getStaticCssUrl()
    {
        return $this->getStaticUrl() . 'css/';
    }

    public function getApiDir()
    {
        return $this->getRootDir() . 'api' . DS;
    }

    public function getApiBolDir()
    {
        return $this->getApiDir() . 'bol' . DS;
    }

    public function getApiCtrlDir()
    {
        return $this->getApiDir() . 'controllers' . DS;
    }

    public function getApiClassesDir()
    {
        return $this->getApiDir() . 'classes' . DS;
    }
}

class PEEP_SystemPlugin extends PEEP_Plugin
{

    public function __construct( $params )
    {
        parent::__construct($params);
    }

    /**
     * @see PEEP_Plugin::getRootDir()
     *
     * @return unknown
     */
    public function getRootDir()
    {
        return PEEP_DIR_SYSTEM_PLUGIN . $this->dirName . DS;
    }
}
