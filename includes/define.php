<?php
define('PEEP_DIR_STATIC_PLUGIN', PEEP_DIR_STATIC . 'plugins' . DS);
define('PEEP_DIR_STATIC_THEME', PEEP_DIR_STATIC . 'themes' . DS);
define('PEEP_DIR_PLUGIN_USERFILES', PEEP_DIR_USERFILES . 'plugins' . DS);
define('PEEP_DIR_THEME_USERFILES', PEEP_DIR_USERFILES . 'themes' . DS);
define('PEEP_DIR_LOG', PEEP_DIR_ROOT . 'log-file' . DS);

if ( defined('PEEP_URL_STATIC') )
{
    define('PEEP_URL_STATIC_THEMES', PEEP_URL_STATIC . 'themes/');
    define('PEEP_URL_STATIC_PLUGINS', PEEP_URL_STATIC . 'plugins/');
}

if ( defined('PEEP_URL_USERFILES') )
{
    define('PEEP_URL_PLUGIN_USERFILES', PEEP_URL_USERFILES . 'plugins/');
    define('PEEP_URL_THEME_USERFILES', PEEP_URL_USERFILES . 'themes/');
}

