<?php


$config = PEEP::getConfig();

if ( !$config->configExists('pvisitors', 'store_period') )
{
    $config->addConfig('pvisitors', 'store_period', 3, 'Visitors visit period, months');
}

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."pvisitors_visitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `visitorId` int(11) NOT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `visitTimestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`visitorId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

PEEP::getPluginManager()->addPluginSettingsRouteName('pvisitors', 'pvisitors.admin');

$path = PEEP::getPluginManager()->getPlugin('pvisitors')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'pvisitors');
