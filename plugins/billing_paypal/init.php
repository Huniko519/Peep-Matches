<?php

PEEP::getRouter()->addRoute(new PEEP_Route('billing_paypal_order_form', 'billing-paypal/order', 'BILLINGPAYPAL_CTRL_Order', 'form'));
PEEP::getRouter()->addRoute(new PEEP_Route('billing_paypal_notify', 'billing-paypal/order/notify', 'BILLINGPAYPAL_CTRL_Order', 'notify'));
PEEP::getRouter()->addRoute(new PEEP_Route('billing_paypal_completed', 'billing-paypal/order/completed/', 'BILLINGPAYPAL_CTRL_Order', 'completed'));
PEEP::getRouter()->addRoute(new PEEP_Route('billing_paypal_canceled', 'billing-paypal/order/canceled/', 'BILLINGPAYPAL_CTRL_Order', 'canceled'));
PEEP::getRouter()->addRoute(new PEEP_Route('billing_paypal_admin', 'admin/billing-paypal', 'BILLINGPAYPAL_CTRL_Admin', 'index'));

BILLINGPAYPAL_CLASS_EventHandler::getInstance()->init();