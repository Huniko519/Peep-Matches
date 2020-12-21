<?php

class BOL_ThemeContent extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $value;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return integer
     */
    public function getThemeId()
    {
        return (int) $this->themeId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param integer $themeId
     * @return BOL_ThemeContent
     */
    public function setThemeId( $themeId )
    {
        $this->themeId = (int) $themeId;
        return $this;
    }

    /**
     * @param string $type
     * @return BOL_ThemeContent
     */
    public function setType( $type )
    {
        $this->type = trim($type);
        return $this;
    }

    /**
     * @param string $value
     * @return BOL_ThemeContent
     */
    public function setValue( $value )
    {
        $this->value = trim($value);
        return $this;
    }
}
