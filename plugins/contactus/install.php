<?php

//BOL_LanguageService::getInstance()->addPrefix('contactus', 'Contact Us');

$sql = "CREATE TABLE `" . PEEP_DB_PREFIX . "contactus_department` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(200) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM
ROW_FORMAT=DEFAULT";
//installing database
PEEP::getDbo()->query($sql);
//installing language pack
PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('contactus')->getRootDir().'langs.zip', 'contactus');
//adding admin settings page
PEEP::getPluginManager()->addPluginSettingsRouteName('contactus', 'contactus.admin');
