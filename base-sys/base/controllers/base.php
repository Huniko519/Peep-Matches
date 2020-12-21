<?php

class BASE_CTRL_Base extends PEEP_ActionController
{
    public function index()
    {
        //TODO implement

    }
    
    public function turnDevModeOn()
    {
        if( PEEP_DEV_MODE || PEEP_PROFILER_ENABLE )
        {
            PEEP::getConfig()->saveConfig('base', 'dev_mode', 1);
        }
        
        if( !empty($_GET['back-uri']) )
        {
            $this->redirect(urldecode($_GET['back-uri']));
        }
        else
        {
            $this->redirect(PEEP_URL_HOME);
        }
    }

    public function robotsTxt()
    {
        if( file_exists(PEEP_DIR_ROOT.'robots.txt') )
        {
            header("Content-Type: text/plain");
            echo(file_get_contents(PEEP_DIR_ROOT.'robots.txt'));
            exit;
        }

        throw new Redirect404Exception();
    }
}