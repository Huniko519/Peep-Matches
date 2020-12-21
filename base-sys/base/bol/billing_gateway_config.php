<?php

class BOL_BillingGatewayConfig extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var int
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $value;
}