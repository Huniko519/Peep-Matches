<?php


$pluginKey = 'spotlight';
$dbPrefix = PEEP_DB_PREFIX.$pluginKey.'_';

$sql =
    <<<EOT

CREATE TABLE IF NOT EXISTS `{$dbPrefix}user` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `expiration_timestamp` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOT;

PEEP::getDbo()->query($sql);

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin($pluginKey)->getRootDir() . 'langs.zip', $pluginKey);

PEEP::getPluginManager()->addPluginSettingsRouteName($pluginKey, 'spotlight-admin-settings');

PEEP::getConfig()->addConfig($pluginKey, 'expiration_time', 86400 * 30);

PEEP::getAuthorization()->addGroup('spotlight', false);
PEEP::getAuthorization()->addAction('spotlight', 'add_to_list');