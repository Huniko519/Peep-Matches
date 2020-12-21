<?php

class BOL_ComponentSetting extends PEEP_Entity
{
    /**
     * @var string
     */
    public $componentPlaceUniqName;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $value;

    /**
     *
     * @var string
     */
    public $type = 'string';

    public function setValue( $value )
    {
        if ( is_array($value) )
        {
            $this->type = 'json';
            $this->value = json_encode($value);
        }
        else
        {
            $this->type = 'string';
            $this->value = $value;
        }
    }

    public function getValue()
    {
        if ( $this->type == 'json' )
        {
            return json_decode($this->value, true);
        }

        return $this->value;
    }
}
