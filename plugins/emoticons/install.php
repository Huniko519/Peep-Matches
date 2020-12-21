<?php


$sql = array(
'CREATE TABLE IF NOT EXISTS `' . PEEP_DB_PREFIX . 'emoticons_emoticons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` int(10) unsigned NOT NULL,
  `isCaption` tinyint(1) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT "0",
  `code` varchar(12) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `order` (`order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',

"INSERT INTO `" . PEEP_DB_PREFIX . "emoticons_emoticons` (`id`, `category`, `isCaption`, `order`, `code`, `name`) VALUES
(NULL, 1, 1, 0, ':)', 'emot.png'),
(NULL, 1, 0, 0, ':D', 'emot2.png'),
(NULL, 1, 0, 0, ';d', 'emot3.png'),
(NULL, 1, 0, 0, ':L', 'emot4.png'),
(NULL, 1, 0, 0, ':G', 'emot5.png'),
(NULL, 1, 0, 0, ':B', 'emot6.png'),
(NULL, 1, 0, 0, '8-)', 'emot7.png'),
(NULL, 1, 0, 0, '(F)', 'emot8.png'),
(NULL, 1, 0, 0, '(CH)', 'emot9.png'),
(NULL, 1, 0, 0, '(sweat)', 'emot10.png'),
(NULL, 1, 0, 0, ':@', 'emot11.png'),
(NULL, 1, 0, 0, '(surprised)', 'emot12.png'),
(NULL, 1, 0, 0, '(SH)', 'emot13.png'),
(NULL, 1, 0, 0, ';(', 'emot14.png'),
(NULL, 1, 0, 0, ':*', 'emot15.png'),
(NULL, 1, 0, 0, '(h)', 'emot16.png'),
(NULL, 1, 0, 0, '(u)', 'emot17.png');"
);

foreach ( $sql as $s )
{
    try
    {
        PEEP::getDbo()->query($s);
    }
    catch ( Exception $ex )
    {
        PEEP::getLogger()->addEntry(json_encode($ex));
    }
}

$config = PEEP::getConfig();

if ( !$config->configExists('emoticons', 'width') )
{
    $config->addConfig('emoticons', 'width', 181);
}

PEEP::getPluginManager()->addPluginSettingsRouteName('emoticons', 'emoticons.admin');

$plugin = PEEP::getPluginManager()->getPlugin('emoticons');
UTIL_File::copyDir($plugin->getStaticDir() . 'emoticons', $plugin->getUserFilesDir() . 'emoticons');

PEEP::getLanguage()->importPluginLangs($plugin->getRootDir() . 'langs.zip', 'emoticons');