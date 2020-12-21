<?php

$config = PEEP::getConfig();

if ( !$config->configExists('photo', 'accepted_filesize') )
{
    $config->addConfig('photo', 'accepted_filesize', 32, 'Maximum accepted file size');
}

if ( !$config->configExists('photo', 'main_image_width') )
{
    $config->addConfig('photo', 'main_image_width', 960, 'Main image width');
}

if ( !$config->configExists('photo', 'main_image_height') )
{
    $config->addConfig('photo', 'main_image_height', 640, 'Main image height');
}

if ( !$config->configExists('photo', 'preview_image_width') )
{
    $config->addConfig('photo', 'preview_image_width', 140, 'Preview image width');
}

if ( !$config->configExists('photo', 'preview_image_height') )
{
    $config->addConfig('photo', 'preview_image_height', 140, 'Preview image height');
}

if ( !$config->configExists('photo', 'photos_per_page') )
{
    $config->addConfig('photo', 'photos_per_page', 20, 'Photos per page');
}

if ( !$config->configExists('photo', 'album_quota') )
{
    $config->addConfig('photo', 'album_quota', 400, 'Maximum number of photos per album');
}

if ( !$config->configExists('photo', 'user_quota') )
{
    $config->addConfig('photo', 'user_quota', 5000, 'Maximum number of photos per user');
}

if ( !$config->configExists('photo', 'store_fullsize') )
{
    $config->addConfig('photo', 'store_fullsize', 1, 'Store full-size photos');
}

if ( !$config->configExists('photo', 'uninstall_inprogress') )
{
    $config->addConfig('photo', 'uninstall_inprogress', 0, 'Plugin is being uninstalled');
}

if ( !$config->configExists('photo', 'uninstall_cron_busy') )
{
    $config->addConfig('photo', 'uninstall_cron_busy', 0, 'Uninstall queue is busy');
}

if ( !$config->configExists('photo', 'maintenance_mode_state') )
{
    $state = (int) $config->getValue('base', 'maintenance');
    $config->addConfig('photo', 'maintenance_mode_state', $state, 'Stores site maintenance mode config before plugin uninstallation');
}

if ( !$config->configExists('photo', 'fullsize_resolution') )
{
    $config->addConfig('photo', 'fullsize_resolution', 1024, 'Full-size photo resolution');
}

if ( !$config->configExists('photo', 'photo_list_view_classic') )
{
    $config->addConfig('photo', 'photo_list_view_classic', FALSE);
}

if ( !$config->configExists('photo', 'album_list_view_classic') )
{
    $config->addConfig('photo', 'album_list_view_classic', FALSE);
}

if ( !$config->configExists('photo', 'photo_view_classic') )
{
    $config->addConfig('photo', 'photo_view_classic', FALSE);
}

if ( !$config->configExists('photo', 'download_accept') )
{
    $config->addConfig('photo', 'download_accept', TRUE);
}

PEEP::getDbo()->query('DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumId` int(11) NOT NULL,
  `description` text,
  `addDatetime` int(10) DEFAULT NULL,
  `status` enum("approval","approved","blocked") NOT NULL DEFAULT "approved",
  `hasFullsize` tinyint(1) NOT NULL DEFAULT "1",
  `privacy` varchar(50) NOT NULL DEFAULT "everybody",
  `hash` varchar(16) DEFAULT NULL,
  `uploadKey` varchar(32) DEFAULT NULL,
  `dimension` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `albumId` (`albumId`),
  KEY `status` (`status`),
  KEY `privacy` (`privacy`),
  KEY `uploadKey` (`uploadKey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_album`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_album` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `entityType` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT "user",
  `entityId` INT NULL DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `createDatetime` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `userId` (`userId`),
  KEY `entityType` (`entityType`),
  KEY `entityId` (`entityId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_featured`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_featured` (
  `id` int(11) NOT NULL auto_increment,
  `photoId` int(11) NOT NULL default "0",
  PRIMARY KEY  (`id`),
  KEY `photoId` (`photoId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_temporary`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_temporary` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) NOT NULL,
  `addDatetime` int(11) NOT NULL,
  `hasFullsize` tinyint(1) NOT NULL default "0",
  `order` int(11) NOT NULL default "0",
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_album_cover`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_album_cover` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumId` int(10) unsigned NOT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `auto` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `albumId` (`albumId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_cache`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` int(11) NOT NULL,
  `data` text NOT NULL,
  `createTimestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_search_data`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_search_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityTypeId` int(10) unsigned NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_search_entity_type`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_search_entity_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityType` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT IGNORE INTO `' . PEEP_DB_PREFIX . 'photo_search_entity_type` (`id`, `entityType`) VALUES
(null, "photo.album"),
(null, "photo.photo");

DROP TABLE IF EXISTS `' . PEEP_DB_PREFIX . 'photo_search_index`;
CREATE TABLE `' . PEEP_DB_PREFIX . 'photo_search_index` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityTypeId` int(10) unsigned NOT NULL,
  `entityId` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityTypeId` (`entityTypeId`,`entityId`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;');

PEEP::getPluginManager()->addPluginSettingsRouteName('photo', 'photo_admin_config');
PEEP::getPluginManager()->addUninstallRouteName('photo', 'photo_uninstall');

$authorization = PEEP::getAuthorization();
$groupName = 'photo';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'upload');
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');

$plugin = PEEP::getPluginManager()->getPlugin('photo');

PEEP::getLanguage()->importPluginLangs($plugin->getRootDir() . 'langs.zip', 'photo');
