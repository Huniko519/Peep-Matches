<?php

$plugin = PEEP::getPluginManager()->getPlugin('googleauth');

BOL_LanguageService::getInstance()->addPrefix('googleauth', 'Auth Google Login');
PEEP::getPluginManager()->addPluginSettingsRouteName('googleauth', 'googleauth_admin_main');

//peep_base_config
PEEP::getConfig()->addConfig('googleauth', 'client_key', '', 'Google Api Key');
PEEP::getConfig()->addConfig('googleauth', 'client_id', '', 'Google Client ID');
PEEP::getConfig()->addConfig('googleauth', 'client_secret', '', 'Google Client Secret');


$path = PEEP::getPluginManager()->getPlugin('googleauth')->getRootDir() . 'langs.zip';
PEEP::getLanguage()->importPluginLangs($path, 'googleauth');
