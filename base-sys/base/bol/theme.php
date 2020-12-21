<?php

class BOL_Theme extends PEEP_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $developerKey;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var boolean
     */
    public $isActive = 0;
    /**
     * @var string
     */
    public $customCss;
    /**
     * @var string
     */
    public $mobileCustomCss;
    /**
     * @var string
     */
    public $customCssFileName;
    /**
     * @var string
     */
    public $sidebarPosition;
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
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return (bool) $this->isActive;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * @param string $description
     * @return BOL_Theme
     */
    public function setDescription( $description )
    {
        $this->description = trim($description);
        return $this;
    }

    /**
     * @param boolean $isActive
     * @return BOL_Theme
     */
    public function setIsActive( $isActive )
    {
        $this->isActive = (boolean) $isActive;
        return $this;
    }

    /**
     * @param string $name
     * @return BOL_Theme
     */
    public function setName( $name )
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * @param string $title
     * @return BOL_Theme
     */
    public function setTitle( $title )
    {
        $this->title = trim($title);
        return $this;
    }

    /**
     * @param string $css
     * @return BOL_Theme
     */
    public function setCustomCss( $css )
    {
        $this->customCss = trim($css);
        return $this;
    }

    public function getCustomCssFileName()
    {
        return $this->customCssFileName;
    }

    public function setCustomCssFileName( $customCssFileName )
    {
        $this->customCssFileName = $customCssFileName;
    }

    public function getSidebarPosition()
    {
        return $this->sidebarPosition;
    }

    public function setSidebarPosition( $sidebarPosition )
    {
        $this->sidebarPosition = $sidebarPosition;
    }

    public function getDeveloperKey()
    {
        return $this->developerKey;
    }

    public function setDeveloperKey( $developerKey )
    {
        $this->developerKey = $developerKey;
    }

    public function getBuild()
    {
        return $this->build;
    }

    public function setBuild( $build )
    {
        $this->build = $build;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function setUpdate( $update )
    {
        $this->update = $update;
    }

    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    public function setLicenseKey( $licenseKey )
    {
        $this->licenseKey = $licenseKey;
    }

    public function getMobileCustomCss()
    {
        return $this->mobileCustomCss;
    }

    public function setMobileCustomCss( $mobileCustomCss )
    {
        $this->mobileCustomCss = $mobileCustomCss;
    }
}
