<?php

class GOOGLELOCATION_BOL_Location extends PEEP_Entity {

    public $entityId;
    /**
     * @var string
     */  
    public $entityType = 'user';
    /**
     * @var string
     */
    public $countryCode;
    /**
     * @var int
     */
    public $address;
    /**
     * @var int
     */
    public $lat = 0;
    /**
     * @var int
     */
    public $lng = 0;
    /**
     * @var int
     */
    public $northEastLat = 0;
    /**
     * @var int
     */
    public $northEastLng = 0;
    /**
     * @var int
     */
    public $southWestLat = 0;
    /**
     * @var int
     */
    public $southWestLng = 0;
    /**
     * @var string
     */
    public $json = '';
}
