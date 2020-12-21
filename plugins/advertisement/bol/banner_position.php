<?php

class ADS_BOL_BannerPosition extends PEEP_Entity
{

    public $bannerId;
  
    public $position;
 
    public $pluginKey;

    public function getBannerId()
    {
        return $this->bannerId;
    }

    public function setBannerId( $bannerId )
    {
        $this->bannerId = $bannerId;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition( $position )
    {
        $this->position = $position;
    }

    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    public function setPluginKey( $pluginKey )
    {
        $this->pluginKey = $pluginKey;
    }
}