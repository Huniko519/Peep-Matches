<?php

class PHOTO_BOL_Photo extends PEEP_Entity
{
    
    public $albumId;

    public $description;

    public $addDatetime;
 
    public $status;

    public $hasFullsize;

    public $privacy;

    public $hash;

    public $uploadKey;
    
    public $dimension;
    
    public function getDimension()
    {
        return $this->dimension;
    }

    public function setDimension( $value )
    {
        $this->dimension = $value;
        
        return $this;
    }
}
