<?php

class ADS_BOL_Banner extends PEEP_Entity
{
   
    public $label;
    
    public $code;

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel( $label )
    {
        $this->label = $label;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode( $code )
    {
        $this->code = $code;
    }
}

