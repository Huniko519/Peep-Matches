<?php

define('CACHECLEANER_DIR_ROOT', dirname(__FILE__));
// BOL_LanguageService::getInstance()->addPrefix('cachecleaner','Cache Extreme');

PEEP::getRouter()->addRoute(new PEEP_Route('cachecleaner.admin', 'admin/plugins/cachecleaner', 'CACHECLEANER_CTRL_Admin', 'index'));
PEEP::getRouter()->addRoute(new PEEP_Route('cachecleaner.about', 'admin/plugins/cachecleaner/about', 'CACHECLEANER_CTRL_Admin', 'about'));
