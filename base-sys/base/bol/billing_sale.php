<?php

class BOL_BillingSale extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var string
     */
    public $pluginKey;
    /**
     * @var string
     */
    public $entityKey;
    /**
     * @var int
     */
    public $entityId;
    /**
     * @var string 
     */
    public $entityDescription;
    /**
     * @var int
     */
    public $gatewayId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $transactionUid;
    /**
     * @var float
     */
    public $price;
    /**
     * @var int
     */
    public $period;
    /**
     * @var int
     */
    public $quantity = 1;
    /**
     * @var float
     */
    public $totalAmount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var boolean
     */
    public $recurring;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $timeStamp;
    /**
     * JSON encoded extra data
     * 
     * @var string
     */
    public $extraData;


    public function getExtraData()
    {
        return mb_strlen($this->extraData) ? json_decode($this->extraData) : null;
    }

    public function setExtraData( array $data )
    {
        $this->extraData = is_array($data) ? json_encode($data) : null;
    }
}