<?php

class BOL_Tag extends PEEP_Entity
{
    public $label;

    /**
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return BOL_Tag
     */
    public function setLabel( $label )
    {
        $this->label = $label;
        return $this;
    }
}