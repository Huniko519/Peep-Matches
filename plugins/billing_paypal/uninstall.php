<?php

$billingService = BOL_BillingService::getInstance();

$billingService->deleteConfig('billingpaypal', 'business');
$billingService->deleteConfig('billingpaypal', 'sandboxMode');

$billingService->deleteGateway('billingpaypal');