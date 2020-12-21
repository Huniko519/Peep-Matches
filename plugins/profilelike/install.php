<?php

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "profilelike`( 
		`id` INT NOT NULL AUTO_INCREMENT, 
		`userId` INT NOT NULL, 
		`profileId` INT NOT NULL,
		 PRIMARY KEY (`id`))
		ENGINE=MyISAM
		ROW_FORMAT=DEFAULT";
PEEP::getDbo()->query($sql);

$config = PEEP::getConfig();

if (!$config->configExists('profilelike', 'thumbnails_in_profile_widget') && !$config->configExists('profilelike', 'thumbnails_in_dashboard_widget'))
{
    $config->addConfig('profilelike', 'thumbnails_in_profile_widget', 7, 'no. of thumbnails in profile widget');
	$config->addConfig('profilelike', 'thumbnails_in_dashboard_widget', 7, 'no. of thumbnails in dashboard widget');
}

BOL_LanguageService::getInstance()->addPrefix('profilelike', 'Like Profiles');
PEEP::getPluginManager()->addPluginSettingsRouteName('profilelike', 'profilelike.admin');
PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('profilelike')->getRootDir().'langs.zip', 'profilelike');