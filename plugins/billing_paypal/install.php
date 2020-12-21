<?php

$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = 'billingpaypal';
$gateway->adapterClassName = 'BILLINGPAYPAL_CLASS_PaypalAdapter';
$gateway->active = 0;
$gateway->mobile = 0;
$gateway->recurring = 1;
$gateway->currencies = 'AUD,BRL,CAD,CZK,DKK,EUR,HKD,HUF,ILS,JPY,MYR,MXN,NOK,NZD,PHP,PLN,GBP,SGD,SEK,CHF,TWD,THB,USD';

$billingService->addGateway($gateway);


$billingService->addConfig('billingpaypal', 'business', '');
$billingService->addConfig('billingpaypal', 'sandboxMode', '0');


PEEP::getPluginManager()->addPluginSettingsRouteName('billingpaypal', 'billing_paypal_admin');

$path = PEEP::getPluginManager()->getPlugin('billingpaypal')->getRootDir() . 'langs.zip';
PEEP::getLanguage()->importPluginLangs($path, 'billingpaypal');