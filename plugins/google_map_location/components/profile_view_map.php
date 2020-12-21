<?php


class GOOGLELOCATION_CMP_ProfileViewMap extends GOOGLELOCATION_CMP_Map
{
    public function  __construct( $location = array(), $params = null )
    {
        //$this->setHeight('200px');
        $this->setZoom(9);
        $this->setMapOptions(array(
            'disableDefaultUI' => "false",
            'draggable' => "false",
            'mapTypeControl' => "false",
            'overviewMapControl' => "false",
            'panControl' => "false",
            'rotateControl' => "false",
            'scaleControl' => "false",
            'scrollwheel' => "false",
            'streetViewControl' => "false",
            'zoomControl' => "false"));

        if ( !empty($location) )
        {
            //$this->setCenter($location['latitude'], $location['longitude']);
            $this->setBounds($location['southWestLat'], $location['southWestLng'], $location['northEastLat'], $location['northEastLng']);
            $this->addPoint($location, $location['address']);
        }        
        parent::__construct($params);
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'map.html');
    }
}