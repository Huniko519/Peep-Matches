<?php

define('_PEEP_', true);
define('DS', DIRECTORY_SEPARATOR);
define('PEEP_DIR_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('PEEP_CRON', true);

require_once(PEEP_DIR_ROOT . 'includes' . DS . 'init.php');

PEEP::getRouter()->setBaseUrl(PEEP_URL_HOME);

date_default_timezone_set(PEEP::getConfig()->getValue('base', 'site_timezone'));
PEEP_Auth::getInstance()->setAuthenticator(new PEEP_SessionAuthenticator());

PEEP::getPluginManager()->initPlugins();
$event = new PEEP_Event(PEEP_EventManager::ON_PLUGINS_INIT);
PEEP::getEventManager()->trigger($event);

PEEP::getThemeManager()->initDefaultTheme();

// setting current theme
$activeThemeName = PEEP::getConfig()->getValue('base', 'selectedTheme');

if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && PEEP::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
{
    PEEP_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(trim($activeThemeName)));
}