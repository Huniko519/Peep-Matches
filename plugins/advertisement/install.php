<?php

PEEP::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "ads_banner` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `code` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

PEEP::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "ads_banner_location` (
  `id` int(11) NOT NULL auto_increment,
  `bannerId` int(11) NOT NULL,
  `location` char(3) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `bannerId` (`bannerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5");

PEEP::getDbo()->query("
   CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "ads_banner_position` (
  `id` int(11) NOT NULL auto_increment,
  `bannerId` int(11) NOT NULL,
  `position` enum('top','right','left','bottom') NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `bannerId` (`bannerId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

$authorization = PEEP::getAuthorization();
$groupName = 'ads';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'hide_ads', true);
$action = BOL_AuthorizationService::getInstance()->findAction('ads', 'hide_ads');
BOL_AuthorizationPermissionDao::getInstance()->deleteByActionId($action->getId());

PEEP::getPluginManager()->addPluginSettingsRouteName('ads', 'ads.admin_settings_index');

$path = PEEP::getPluginManager()->getPlugin('ads')->getRootDir() . 'langs.zip';
PEEP::getLanguage()->importPluginLangs($path, 'ads');

$sqlFile = PEEP::getPluginManager()->getPlugin( 'ads' )->getRootDir()."countries.sql";

$plugin = PEEP::getPluginManager()->getPlugin('ads');

$dbPrefix = PEEP_DB_PREFIX;

 if ( !($fd = @fopen($sqlFile, 'rb')) ) {
            throw new LogicException('SQL dump file `'.$sqlFile.'` not found');
        }

 $lineNo = 0;
        $query = '';
        while ( false !== ($line = fgets($fd, 10240)) )
        {
            $lineNo++;

            if ( !strlen(($line = trim($line)))
                || $line{0} == '#' || $line{0} == '-'
                || preg_match('~^/\*\!.+\*/;$~siu', $line) ) {
                continue;
            }

            $query .= $line;

            if ( $line{strlen($line)-1} != ';' ) {
                continue;
            }

            $query = str_replace('%%TBL-PREFIX%%', PEEP_DB_PREFIX, $query);

            try {
                PEEP::getDbo()->query($query);
            }
            catch ( Exception $e ) {
                throw new LogicException('<b>includes/config.php</b> file is incorrect. Update it with details provided below.');
            }

            $query = '';
        }

        fclose($fd);


