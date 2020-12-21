<?php

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "privacy_action_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`key`),
  KEY `key` (`key`),
  KEY `pluginKey` (`pluginKey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

PEEP::getDbo()->query($sql);


$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "privacy_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `action` varchar(32) NOT NULL,
  `value` varchar(50) NOT NULL,
  `inProcess` tinyint(1) NOT NULL default '0',
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`,`action`,`inProcess`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('privacy')->getRootDir() . 'langs.zip', 'privacy');

