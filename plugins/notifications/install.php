<?php

$plugin = PEEP::getPluginManager()->getPlugin('notifications');

$dbPrefix = PEEP_DB_PREFIX;

$sql =
<<<EOT
CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(255) NOT NULL,
  `entityId` varchar(64) NOT NULL,
  `action` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL DEFAULT '0',
  `sent` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_rule` (
  `id` int(11) NOT NULL auto_increment,
  `action` varchar(255) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_userId` (`action`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_unsubscribe` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_send_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$dbPrefix}notifications_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `schedule` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

EOT;

PEEP::getDbo()->query($sql);

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'notifications');