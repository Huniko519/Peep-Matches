<?php

class BOL_BillingGatewayProduct extends PEEP_Entity
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
    public $pluginKey;
    /**
     * @var string
     */
    public $entityType;
    /**
     * @var int
     */
    public $entityId;
    /**
     * @var string
     */
    public $productId;
}