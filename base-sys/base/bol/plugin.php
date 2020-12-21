<?php

class BOL_Plugin extends PEEP_Entity
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $module;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $developerKey;
    /**
     * @var boolean
     */
    public $isSystem;
    /**
     * @var boolean
     */
    public $isActive;
    /**
     * @var string
     */
    public $adminSettingsRoute;
    /**
     * @var string
     */
    public $uninstallRoute;
    /**
     * @var integer
     */
    public $build = 0;
    /**
     * @var boolean
     */
    public $update = 0;
    /**
     * @var string
     */
    public $licenseKey;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return (bool) $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return (bool) $this->isSystem;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     */
    public function setDescription( $description )
    {
        $this->description = trim($description);
        return $this;
    }

    /**
     * @param boolean $isActive
     * @return BOL_Plugin
     */
    public function setIsActive( $isActive )
    {
        $this->isActive = (boolean) $isActive;
        return $this;
    }

    /**
     * @param string $key
     * @return BOL_Plugin
     */
    public function setKey( $key )
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * @param string $module
     * @return BOL_Plugin
     */
    public function setModule( $module )
    {
        $this->module = trim($module);
        return $this;
    }

    /**
     * @param string $title
     * @return BOL_Plugin
     */
    public function setTitle( $title )
    {
        $this->title = trim($title);
        return $this;
    }

    /**
     * @param boolean $isSystem
     * @return BOL_Plugin
     */
    public function setIsSystem( $isSystem )
    {
        $this->isSystem = $isSystem;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminSettingsRoute()
    {
        return $this->adminSettingsRoute;
    }

    /**
     * @param string $adminSettingsRoute
     */
    public function setAdminSettingsRoute( $adminSettingsRoute )
    {
        $this->adminSettingsRoute = $adminSettingsRoute;
    }

    public function getBuild()
    {
        return $this->build;
    }

    public function setBuild( $build )
    {
        $this->build = (int) $build;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function setUpdate( $update )
    {
        $this->update = (int) $update;
    }

    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    public function setLicenseKey( $licenseKey )
    {
        $this->licenseKey = $licenseKey;
    }

    public function getDeveloperKey()
    {
        return $this->developerKey;
    }

    public function setDeveloperKey( $developerKey )
    {
        $this->developerKey = $developerKey;
    }

    public function getUninstallRoute()
    {
        return $this->uninstallRoute;
    }

    public function setUninstallRoute( $uninstallRoute )
    {
        $this->uninstallRoute = $uninstallRoute;
    }
}
