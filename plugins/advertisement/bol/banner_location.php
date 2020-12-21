<?php

class ADS_BOL_BannerLocation extends PEEP_Entity
{
 
    public $bannerId;
 
    public $location;

    public function getBannerId()
    {
        return $this->bannerId;
    }

    public function setBannerId( $bannerId )
    {
        $this->bannerId = $bannerId;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation( $location )
    {
        $this->location = $location;
    }
}

