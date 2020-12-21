<?php


if ( !PEEP::getConfig()->configExists('mailbox', 'results_per_page') )
{
    PEEP::getConfig()->addConfig('mailbox', 'results_per_page', 10, 'Conversations number per page');
}

if ( !PEEP::getConfig()->configExists('mailbox', 'enable_attachments') )
{
    PEEP::getConfig()->addConfig('mailbox', 'enable_attachments', true, 'Enable file attachments');
}

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "mailbox_conversation` (
  `id` int(10) NOT NULL auto_increment,
  `initiatorId` int(10) NOT NULL default '0',
  `interlocutorId` int(10) NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  `read` tinyint(3) NOT NULL default '1' COMMENT 'bitmap, values: 0 - none, 1 - read by initiator, 2 - read by interlocutor, 3 - read all',
  `deleted` tinyint(3) NOT NULL default '0' COMMENT 'bitmap, values: 0 - none, 1 - deleted by initiator, 2 - deleted by interlocutor.',
  `viewed` tinyint(3) NOT NULL default '1' COMMENT 'bitmap, is user viewed conversation in console, values: 0 - none, 1 - viewed by initiator, 2 - viewed by interlocutor, 3 - viewed all',
  `notificationSent` tinyint(3) NOT NULL default '0' COMMENT 'int flag, was notification about this letter sent to user',
  `createStamp` int(10) default '0',
  `initiatorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0',
  `interlocutorDeletedTimestamp` INT( 10 ) NOT NULL DEFAULT  '0',
  `lastMessageId` int(11) NOT NULL,
  `lastMessageTimestamp` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `initiatorId` (`initiatorId`),
  KEY `interlocutorId` (`interlocutorId`),
  KEY `lastMessageTimestamp` (`lastMessageTimestamp`),
  KEY `subject` (`subject`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "mailbox_last_message` (
  `id` int(10) NOT NULL auto_increment,
  `conversationId` int(10) NOT NULL default '0',
  `initiatorMessageId` int(10) NOT NULL default '0',
  `interlocutorMessageId` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `conversationId` (`conversationId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "mailbox_message` (
  `id` int(10) NOT NULL auto_increment,
  `conversationId` int(10) NOT NULL default '0',
  `timeStamp` bigint(10) NOT NULL default '0',
  `senderId` int(10) NOT NULL default '0',
  `recipientId` int(10) NOT NULL default '0',
  `text` mediumtext NOT NULL,
  `recipientRead` TINYINT NOT NULL DEFAULT '0',
  `isSystem` TINYINT NOT NULL DEFAULT  '0',
  `wasAuthorized` TINYINT NOT NULL DEFAULT  '0',
  PRIMARY KEY  (`id`),
  KEY `senderId` (`senderId`),
  KEY `recipientId` (`recipientId`),
  KEY `conversationId` (`conversationId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "mailbox_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messageId` int(11) NOT NULL,
  `hash` varchar(13) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileSize` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `messageId` (`messageId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "mailbox_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messageId` int(11) NOT NULL,
  `hash` varchar(13) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `fileSize` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `messageId` (`messageId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

PEEP::getDbo()->query($sql);

$sql = "CREATE TABLE `" . PEEP_DB_PREFIX . "mailbox_user_last_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

PEEP::getDbo()->query($sql);

//
//$authorization = PEEP::getAuthorization();
//$groupName = 'mailbox';
//$authorization->addGroup($groupName, 0);
//$authorization->addAction($groupName, 'read_message');
//$authorization->addAction($groupName, 'send_message');
//$authorization->addAction($groupName, 'reply_to_message');
//
//$authorization->addAction($groupName, 'read_chat_message');
//$authorization->addAction($groupName, 'send_chat_message');
//$authorization->addAction($groupName, 'reply_to_chat_message');

require_once PEEP_DIR_PLUGIN . 'mailbox' . DS . 'alloc.php';

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_create_conversation_stamp');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_create_conversation_stamp';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_create_conversation_display_capcha');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_create_conversation_display_capcha';
$preference->sectionName = 'general';
$preference->defaultValue = false;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('mailbox')->getRootDir() . 'langs.zip', 'mailbox');

PEEP::getPluginManager()->addPluginSettingsRouteName('mailbox', 'mailbox_admin_config');

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_user_settings_enable_sound');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_user_settings_enable_sound';
$preference->defaultValue = true;
$preference->sectionName = 'general';
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('mailbox_user_settings_show_online_only');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'mailbox_user_settings_show_online_only';
$preference->defaultValue = true;
$preference->sectionName = 'general';
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$modes = array('mail', 'chat');
PEEP::getConfig()->addConfig('mailbox', 'active_modes', json_encode($modes));
PEEP::getConfig()->addConfig('mailbox', 'show_all_members', false);
PEEP::getConfig()->addConfig('mailbox', 'updated_to_messages', 1);
PEEP::getConfig()->addConfig('mailbox', 'install_complete', 0);
PEEP::getConfig()->addConfig('mailbox', 'last_attachment_id', 0);
PEEP::getConfig()->addConfig('mailbox', 'plugin_update_timestamp', 0);
PEEP::getConfig()->addConfig('mailbox', 'send_message_interval', 60);