<?php

class BOL_LanguagePrefix extends PEEP_Entity
{
    /**
     * @var string
     */
    public $prefix;
    /**
     *
     * @var string
     */
    public $label;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $label
     * @return BOL_LanguagePrefix
     */
    public function setLabel( $label )
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $prefix
     * @return BOL_LanguagePrefix
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;

        return $this;
    }
}

