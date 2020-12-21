<?php
/* Peepmatches Light By Peepdev co - www.peepdev.com */
define('_PEEP_', true);

define('DS', DIRECTORY_SEPARATOR);

define('PEEP_DIR_ROOT', dirname(__FILE__) . DS);

require_once(PEEP_DIR_ROOT . 'includes' . DS . 'init.php');

if ( !defined('PEEP_ERROR_LOG_ENABLE') || (bool) PEEP_ERROR_LOG_ENABLE )
{
    $logFilePath = PEEP_DIR_LOG . 'error.log';
    $logger = PEEP::getLogger('core_log');
    $logger->setLogWriter(new BASE_CLASS_FileLogWriter($logFilePath));
    $errorManager->setLogger($logger);
}

@include PEEP_DIR_ROOT . 'install' . DS . 'install.php';

PEEP::getSession()->start();

$application = PEEP::getApplication();

if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('before_app_init');
}

$application->init();

if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_app_init');
}

$event = new PEEP_Event(PEEP_EventManager::ON_APPLICATION_INIT);

PEEP::getEventManager()->trigger($event);

$application->route();

$event = new PEEP_Event(PEEP_EventManager::ON_AFTER_ROUTE);

if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_route');
}

PEEP::getEventManager()->trigger($event);

$application->handleRequest();

if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_controller_call');
}

$event = new PEEP_Event(PEEP_EventManager::ON_AFTER_REQUEST_HANDLE);

PEEP::getEventManager()->trigger($event);

$application->finalize();

if ( PEEP_PROFILER_ENABLE || PEEP_DEV_MODE )
{
    UTIL_Profiler::getInstance()->mark('after_finalize');
}

$application->returnResponse();
