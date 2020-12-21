<?php

$installComplete = false;
$dbReady = false;

if ( defined('PEEP_URL_HOME') )
{
    try
    {
        $installedValue = (bool) PEEP::getConfig()->getValue('base', 'site_installed');
        $installComplete = (bool) PEEP::getConfig()->getValue('base', 'install_complete');
    }
    catch ( Exception $e )
    {
        $installedValue = false;
		$installComplete = false;
    }

    $dbReady = $installedValue;
}

if ( !$installComplete || ( defined('PEEP_INSTALL_DEV') && PEEP_INSTALL_DEV ) )
{
    if ( !defined('PEEP_URL_HOME') )
    {
        $selfUrl = UTIL_Url::selfUrl();

        if ( substr($selfUrl, -1) != '/' )
        {
            $selfUrl .= '/';
        }

        $installPos = strpos($selfUrl, '/install');

        if ( !$installPos )
        {
            $installPos = strpos($selfUrl, '/install');
        }

        if ( $installPos )
        {
            $selfUrl = substr($selfUrl, 0, $installPos) . '/';
        }

        define('PEEP_URL_HOME', $selfUrl);
    }

    define('INSTALL_DIR_ROOT', dirname(__FILE__) . DS);
    define('INSTALL_URL_ROOT', PEEP_URL_HOME . 'install/');

    define('INSTALL_URL_VIEW', INSTALL_URL_ROOT . 'view/');

    define('INSTALL_DIR_CLASSES', INSTALL_DIR_ROOT . 'classes' . DS);
    define('INSTALL_DIR_BOL', INSTALL_DIR_ROOT . 'bol' . DS);
    define('INSTALL_DIR_CTRL', INSTALL_DIR_ROOT . 'controllers' . DS);
    define('INSTALL_DIR_CMP', INSTALL_DIR_ROOT . 'components' . DS);
    define('INSTALL_DIR_VIEW', INSTALL_DIR_ROOT . 'view' . DS);
    define('INSTALL_DIR_VIEW_CTRL', INSTALL_DIR_VIEW . 'controllers' . DS);
    define('INSTALL_DIR_VIEW_CMP', INSTALL_DIR_VIEW . 'components' . DS);
    define('INSTALL_DIR_FILES', INSTALL_DIR_ROOT . 'files' . DS);

    PEEP::getAutoloader()->addPackagePointer('INSTALL', INSTALL_DIR_CLASSES);
    PEEP::getAutoloader()->addPackagePointer('INSTALL_BOL', INSTALL_DIR_BOL);
    PEEP::getAutoloader()->addPackagePointer('INSTALL_CTRL', INSTALL_DIR_CTRL);
    PEEP::getAutoloader()->addPackagePointer('INSTALL_CMP', INSTALL_DIR_CMP);

    PEEP::getAutoloader()->addClass('INSTALL', INSTALL_DIR_CLASSES . 'install.php');

    PEEP::getSession()->start();

    $application = INSTALL_Application::getInstance();

    $application->init($dbReady);

    $application->display($dbReady);

    exit;
}