<?php

interface PEEP_BillingProductAdapter
{
    /**
     * Returns the key of a product being sold.
     * Product key is stored as 'entityKey' field in BOL_BillingSale object 
     */
    public function getProductKey();

    /**
     * Returns product order page url
     */
    public function getProductOrderUrl();

    /**
     * Method is called to finalize sale.
     * Sets sale status to 'delivered'
     * 
     * @param BOL_BillingSale $sale
     */
    public function deliverSale( BOL_BillingSale $sale );
}