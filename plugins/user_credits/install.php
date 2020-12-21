<?php

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."usercredits_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."usercredits_action` (
  `id` int(11) NOT NULL auto_increment,
  `pluginKey` varchar(100) NOT NULL,
  `actionKey` varchar(100) NOT NULL,
  `isHidden` tinyint(1) NOT NULL default '0',
  `settingsRoute` VARCHAR( 255 ) NULL DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `action` (`pluginKey`,`actionKey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."usercredits_action_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actionId` int(11) NOT NULL,
  `accountTypeId` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `disabled` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `actionPrice` (`actionId`,`accountTypeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."usercredits_pack` (
  `id` int(11) NOT NULL auto_increment,
  `accountTypeId` INT NULL DEFAULT NULL,
  `credits` int(11) NOT NULL,
  `price` float(9,3) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `".PEEP_DB_PREFIX."usercredits_log` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `actionId` int(11) default NULL,
  `amount` int(11) NOT NULL default '0',
  `logTimestamp` int(11) NOT NULL default '0',
  `additionalParams` VARCHAR(2048) default NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`),
  KEY `actionId` (`actionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

PEEP::getDbo()->query($sql);

try {
    $product = new BOL_BillingProduct();
    $product->active = 1;
    $product->productKey = 'user_credits_pack';
    $product->adapterClassName = 'USERCREDITS_CLASS_UserCreditsPackProductAdapter';
    
    BOL_BillingService::getInstance()->saveProduct($product);
}
catch ( Exception $e ) { }

if ( !PEEP::getConfig()->configExists('usercredits', 'allow_grant_credits') )
{
    PEEP::getConfig()->addConfig('usercredits', 'allow_grant_credits', "1");
}

PEEP::getPluginManager()->addPluginSettingsRouteName('usercredits', 'usercredits.admin');

$authorization = PEEP::getAuthorization();
$groupName = 'usercredits';
$authorization->addGroup($groupName);

$path = PEEP::getPluginManager()->getPlugin('usercredits')->getRootDir() . 'langs.zip';
PEEP::getLanguage()->importPluginLangs($path, 'usercredits');
