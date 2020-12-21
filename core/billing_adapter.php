<?php

interface PEEP_BillingAdapter
{

    /**
     * Prepairs sale
     * 
     * @param BOL_BillingSale $sale
     */
    public function prepareSale( BOL_BillingSale $sale );

    /**
     * Sets sale status verified
     * 
     * @param BOL_BillingSale $sale
     */
    public function verifySale( BOL_BillingSale $sale );

    /**
     * Returns gateway setting fields
     * 
     * @return array
     */
    public function getFields( $params = null );

    /**
     * Returns order form page url
     * 
     * @return string
     */
    public function getOrderFormUrl();

    /**
     * Returns gateway logo url
     * 
     * @return string
     */
    public function getLogoUrl();
}