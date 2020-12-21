<?php

PEEP::getPluginManager()->addPluginSettingsRouteName('stories', 'stories-admin');

PEEP::getPluginManager()->addUninstallRouteName('stories', 'stories-uninstall');

$dbPrefix = PEEP_DB_PREFIX;

$sql =
    <<<EOT

CREATE TABLE `{$dbPrefix}stories_post` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `authorId` INTEGER(11) NOT NULL,
  `title` VARCHAR(512) COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `post` TEXT COLLATE utf8_general_ci NOT NULL,
  `timestamp` INTEGER(11) NOT NULL,
  `isDraft` TINYINT(1) NOT NULL,
  `privacy` varchar(50) NOT NULL default 'everybody',
  PRIMARY KEY (`id`),
  KEY `authorId` (`authorId`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOT;

PEEP::getDbo()->query($sql);

if ( !PEEP::getConfig()->configExists('stories', 'results_per_page') )
{
    PEEP::getConfig()->addConfig('stories', 'results_per_page', 10, 'Post number per page');
}

if ( !PEEP::getConfig()->configExists('stories', 'uninstall_inprogress') )
{
    PEEP::getConfig()->addConfig('stories', 'uninstall_inprogress', 0, '');
}

if ( !PEEP::getConfig()->configExists('stories', 'uninstall_cron_busy') )
{
    PEEP::getConfig()->addConfig('stories', 'uninstall_cron_busy', 0, '');
}

$authorization = PEEP::getAuthorization();
$groupName = 'stories';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'add');
$authorization->addAction($groupName, 'view', true);

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('stories')->getRootDir() . 'langs.zip', 'stories');