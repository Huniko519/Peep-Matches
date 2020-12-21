<?php

class BOL_BillingProduct extends PEEP_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * For unique values use the following convention: 
     * 'pluginkey_your_product_key'
     * 
     * @var string
     */
    public $productKey;
    /**
     * @var string
     */
    public $adapterClassName;
    /**
     * @var boolean
     */
    public $active;
}