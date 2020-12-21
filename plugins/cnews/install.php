<?php

$plugin = PEEP::getPluginManager()->getPlugin('cnews');

$dbPrefix = PEEP_DB_PREFIX;

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` VARCHAR(100) NOT NULL,
  `entityType` varchar(100) NOT NULL,
  `pluginKey` varchar(100) NOT NULL,
  `data` longtext NOT NULL,
  `format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity` (`entityType`,`entityId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE `{$dbPrefix}cnews_action_feed` (
  `id` int(11) NOT NULL auto_increment,
  `feedType` varchar(100) NOT NULL,
  `feedId` int(11) NOT NULL,
  `activityId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `feedId` (`feedType`,`feedId`,`activityId`),
  KEY `actionId` (`activityId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE `{$dbPrefix}cnews_activity` (
  `id` int(11) NOT NULL auto_increment,
  `activityType` varchar(100) NOT NULL,
  `activityId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `data` text NOT NULL,
  `actionId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `privacy` varchar(100) NOT NULL,
  `visibility` int(11) NOT NULL,
  `status` varchar(100) NOT NULL default 'active',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `activityId` (`activityId`,`activityType`,`actionId`),
  KEY `actionId` (`actionId`),
  KEY `userId` (`userId`),
  KEY `activityType` ( `activityType`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_follow` (
  `id` int(11) NOT NULL,
  `feedId` int(11) NOT NULL,
  `feedType` varchar(60) NOT NULL,
  `userId` int(11) NOT NULL,
  `permission` varchar(60) NOT NULL DEFAULT 'everybody',
  `followTime` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$sql[] ="ALTER TABLE `{$dbPrefix}cnews_follow` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `feedId` (`feedId`,`userId`,`feedType`,`permission`), ADD KEY `userId` (`userId`);";
$sql[] ="ALTER TABLE `{$dbPrefix}cnews_follow` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_cron_command` (
  `id` int(11) NOT NULL auto_increment,
  `command` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `processData` text NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_like` (
  `id` int(11) NOT NULL auto_increment,
  `entityType` varchar(100) NOT NULL,
  `entityId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_status` (
  `id` int(11) NOT NULL auto_increment,
  `feedType` varchar(100) NOT NULL,
  `feedId` int(11) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `feedType` (`feedType`,`feedId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] ="
CREATE TABLE IF NOT EXISTS `{$dbPrefix}cnews_action_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actionId` (`actionId`,`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

foreach ( $sql as $query )
{
    PEEP::getDbo()->query($query);
}

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'cnews');

PEEP::getConfig()->addConfig('cnews', 'allow_likes', 1, 'Allow Likes');
PEEP::getConfig()->addConfig('cnews', 'allow_comments', 1, 'Allow Comments');

PEEP::getConfig()->addConfig('cnews', 'comments_count', 3, 'Count of comments');
PEEP::getConfig()->addConfig('cnews', 'index_status_enabled', 1, 'Index status is enabled');
PEEP::getConfig()->addConfig('cnews', 'features_expanded', 1, 'Comments and likes box is expanded');
PEEP::getConfig()->addConfig('cnews', 'disabled_action_types', '');

PEEP::getPluginManager()->addPluginSettingsRouteName('cnews', 'cnews_admin_settings');

$authorization = PEEP::getAuthorization();
$groupName = 'cnews';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'allow_status_update');

$event = new BASE_CLASS_EventCollector('feed.collect_follow');
PEEP::getEventManager()->trigger($event);

foreach ( $event->getData() as $follow )
{
    $dbTbl = PEEP_DB_PREFIX . 'cnews_follow';
    $follow['permission'] = empty($follow['permission']) ? 'everybody' : $follow['permission'];

    $query = "REPLACE INTO $dbTbl SET feedType=:ft, feedId=:f, userId=:u, followTime=:t, permission=:p";
    PEEP::getDbo()->query($query, array(
        'ft' => trim($follow['feedType']),
        'u' => (int) $follow['feedId'],
        'f' => (int) $follow['userId'],
        'p' => $follow['permission'],
        't' => time()
    ));
}

$preference = BOL_PreferenceService::getInstance()->findPreference('cnews_generate_action_set_timestamp');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'cnews_generate_action_set_timestamp';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 10000;

BOL_PreferenceService::getInstance()->savePreference($preference);