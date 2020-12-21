<?php

class BOL_BillingGateway extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $gatewayKey;
    /**
     * @var string
     */
    public $adapterClassName;
    /**
     * @var boolean
     */
    public $active;
    /**
     * @var boolean
     */
    public $mobile;
    /**
     * @var boolean
     */
    public $recurring;
    /**
     * @var boolean
     */
    public $dynamic;
    /**
     * @var boolean
     */
    public $hidden = 0;
    /**
     * @var string
     */
    public $currencies;

    public function getCurrenciesString()
    {
        return str_replace(',', ', ', $this->currencies);
    }
}