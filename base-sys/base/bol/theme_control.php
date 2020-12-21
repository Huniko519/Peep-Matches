<?php

class BOL_ThemeControl extends PEEP_Entity
{
    /**
     * @var string
     */
    public $attribute;
    /**
     * @var string
     */
    public $selector;
    /**
     * @var mixed
     */
    public $defaultValue;
    /**
     * @var string
     */
    public $type;
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $section;
    /**
     * @var string
     */
    public $label;
    /**
     * @var string
     */
    public $description;
    /**
     * @var boolean
     */
    public $mobile;

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getThemeId()
    {
        return $this->themeId;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = trim($description);
    }

    /**
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $label
     */
    public function setLabel( $label )
    {
        $this->label = $label;
    }

    /**
     * @param string $key
     */
    public function setKey( $key )
    {
        $this->key = $key;
    }

    /**
     * @param string $attribute
     * @return BOL_ThemeControl
     */
    public function setAttribute( $attribute )
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param string $selector
     * @return BOL_ThemeControl
     */
    public function setSelector( $selector )
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @param string $defaultValue
     * @return BOL_ThemeControl
     */
    public function setDefaultValue( $defaultValue )
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     *
     * @param string $type
     * @return BOL_ThemeControl
     */
    public function setType( $type )
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @param integer $themeId
     * @return BOL_ThemeControl
     */
    public function setThemeId( $themeId )
    {
        $this->themeId = $themeId;
        return $this;
    }

    /**
     * @param string $section
     * @return BOL_ThemeControl
     */
    public function setSection( $section )
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return bool
     */
    public function getMobile()
    {
        return (bool) $this->mobile;
    }

    /**
     * @param bool $mobile
     */
    public function setMobile( $mobile )
    {
        $this->mobile = (bool) $mobile;
    }
}