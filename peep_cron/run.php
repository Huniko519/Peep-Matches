<?php

define('_PEEP_', true);

define('DS', DIRECTORY_SEPARATOR);

define('PEEP_DIR_ROOT', substr(dirname(__FILE__), 0, - strlen('peep_cron')));

define('PEEP_CRON', true);

require_once(PEEP_DIR_ROOT . 'includes' . DS . 'init.php');

// set error log file
if ( !defined('PEEP_ERROR_LOG_ENABLE') || (bool) PEEP_ERROR_LOG_ENABLE )
{
    $logFilePath = PEEP_DIR_LOG . 'cron_error.log';
    $logger = PEEP::getLogger('core_log');
    $logger->setLogWriter(new BASE_CLASS_FileLogWriter($logFilePath));
    $errorManager->setLogger($logger);
}

if ( !isset($_GET['peep-light-cron']) && !PEEP::getConfig()->getValue('base', 'cron_is_configured') )
{
    if ( PEEP::getConfig()->configExists('base', 'cron_is_configured') )
    {
        PEEP::getConfig()->saveConfig('base', 'cron_is_configured', 1);
    }
    else
    {
        PEEP::getConfig()->addConfig('base', 'cron_is_configured', 1);
    }
}

PEEP::getRouter()->setBaseUrl(PEEP_URL_HOME);

date_default_timezone_set(PEEP::getConfig()->getValue('base', 'site_timezone'));
PEEP_Auth::getInstance()->setAuthenticator(new PEEP_SessionAuthenticator());

PEEP::getPluginManager()->initPlugins();
$event = new PEEP_Event(PEEP_EventManager::ON_PLUGINS_INIT);
PEEP::getEventManager()->trigger($event);

//init cache manager
$beckend = PEEP::getEventManager()->call('base.cache_backend_init');

if ( $beckend !== null )
{
    PEEP::getCacheManager()->setCacheBackend($beckend);
    PEEP::getCacheManager()->setLifetime(3600);
    PEEP::getDbo()->setUseCashe(true);
}

PEEP::getThemeManager()->initDefaultTheme();

// setting current theme
$activeThemeName = PEEP::getConfig()->getValue('base', 'selectedTheme');

if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && PEEP::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
{
    PEEP_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByName(trim($activeThemeName)));
}

$plugins = BOL_PluginService::getInstance()->findActivePlugins();

foreach ( $plugins as $plugin )
{
    /* @var $plugin BOL_Plugin */
    $pluginRootDir = PEEP::getPluginManager()->getPlugin($plugin->getKey())->getRootDir();
    if ( file_exists($pluginRootDir . 'cron.php') )
    {
        include $pluginRootDir . 'cron.php';
        $className = strtoupper($plugin->getKey()) . '_Cron';
        $cron = new $className;

        $runJobs = array();
        $newRunJobDtos = array();

        foreach ( BOL_CronService::getInstance()->findJobList() as $runJob )
        {
            /* @var $runJob BOL_CronJob */
            $runJobs[$runJob->methodName] = $runJob->runStamp;
        }

        $jobs = $cron->getJobList();

        foreach ( $jobs as $job => $interval )
        {
            $methodName = $className . '::' . $job;
            $runStamp = ( isset($runJobs[$methodName]) ) ? $runJobs[$methodName] : 0;
            $currentStamp = time();
            if ( ( $currentStamp - $runStamp ) > ( $interval * 60 ) )
            {
                $runJobDto = new BOL_CronJob();
                $runJobDto->methodName = $methodName;
                $runJobDto->runStamp = $currentStamp;
                $newRunJobDtos[] = $runJobDto;

                BOL_CronService::getInstance()->batchSave($newRunJobDtos);

                $newRunJobDtos = array();

                $cron->$job();
            }
        }
    }
}
