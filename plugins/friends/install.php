<?php

$dbPrefix = PEEP_DB_PREFIX;

$sql =
"CREATE TABLE IF NOT EXISTS `{$dbPrefix}friends_friendship` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `friendId` int(11) NOT NULL,
  `status` enum('active','pending','ignored') NOT NULL default 'pending',
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL default '1',
  `notificationSent` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId_friendId` (`userId`,`friendId`),
  KEY `friendId` (`friendId`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

PEEP::getDbo()->query($sql);

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('friends')->getRootDir() . 'langs.zip', 'friends');

PEEP::getAuthorization()->addGroup('friends', false);
PEEP::getAuthorization()->addAction('friends', 'add_friend');