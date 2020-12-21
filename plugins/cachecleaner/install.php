<?php

$path = PEEP::getPluginManager()->getPlugin('cachecleaner')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'cachecleaner');

PEEP::getPluginManager()->addPluginSettingsRouteName('cachecleaner', 'cachecleaner.admin');

PEEP::getConfig()->addConfig('cachecleaner', 'template_cache', true);
PEEP::getConfig()->addConfig('cachecleaner', 'backend_cache', true);
PEEP::getConfig()->addConfig('cachecleaner', 'theme_static', true);
PEEP::getConfig()->addConfig('cachecleaner', 'plugin_static', true);