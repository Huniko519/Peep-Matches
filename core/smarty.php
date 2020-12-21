<?php
require_once(PEEP_DIR_LIB . 'smarty3' . DS . 'Smarty.class.php');

class PEEP_Smarty extends Smarty
{

    public function __construct()
    {
        parent::__construct();

        $this->compile_check = false;
        $this->force_compile = false;
        $this->caching = false;
        $this->debugging = false;

        if ( PEEP_DEV_MODE )
        {
            $this->compile_check = true;
            $this->force_compile = true;
        }

        $this->cache_dir = PEEP_DIR_SMARTY . 'cache' . DS;
        $this->compile_dir = PEEP_DIR_SMARTY . 'bodycache' . DS;
        $this->addPluginsDir(PEEP_DIR_SMARTY . 'plugin' . DS);
        $this->enableSecurity('PEEP_Smarty_Security');
    }
}

class PEEP_Smarty_Security extends Smarty_Security
{

    public function __construct( $smarty )
    {
        parent::__construct($smarty);
        $this->secure_dir = array(PEEP_DIR_THEME, PEEP_DIR_SYSTEM_PLUGIN, PEEP_DIR_PLUGIN);
        $this->php_functions = array('array', 'list', 'isset', 'empty', 'count', 'sizeof', 'in_array', 'is_array', 'true', 'false', 'null', 'strstr');
        $this->php_modifiers = array('count');
        $this->allow_constants = false;
        $this->allow_super_globals = false;
        $this->static_classes = null;
    }
}