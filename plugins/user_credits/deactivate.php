<?php

BOL_BillingService::getInstance()->deactivateProduct('user_credits_pack');

PEEP::getConfig()->saveConfig('usercredits', 'is_once_initialized', 0);

BOL_ComponentAdminService::getInstance()->deleteWidget('USERCREDITS_CMP_MyCreditsWidget');