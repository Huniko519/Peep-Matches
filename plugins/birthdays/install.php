<?php


if( !PEEP::getConfig()->configExists('birthdays', 'users_birthday_event_ts') )
{
    PEEP::getConfig()->addConfig('birthdays', 'users_birthday_event_ts', '0');
}

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('birthdays')->getRootDir() . 'langs.zip', 'birthdays');

PEEP::getDbo()->query("
CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "birthdays_privacy` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `privacy` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
