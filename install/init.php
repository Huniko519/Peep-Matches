<?php

PEEP::getRouter()->addRoute(new PEEP_Route('requirements', 'install', 'INSTALL_CTRL_Install', 'requirements'));
PEEP::getRouter()->addRoute(new PEEP_Route('site', 'install/configuration', 'INSTALL_CTRL_Install', 'site'));
PEEP::getRouter()->addRoute(new PEEP_Route('db', 'install/dblink', 'INSTALL_CTRL_Install', 'db'));

PEEP::getRouter()->addRoute(new PEEP_Route('install', 'install/installation', 'INSTALL_CTRL_Install', 'install'));
PEEP::getRouter()->addRoute(new PEEP_Route('install-action', 'install/installation/:action', 'INSTALL_CTRL_Install', 'install'));

PEEP::getRouter()->addRoute(new PEEP_Route('plugins', 'install/plugins', 'INSTALL_CTRL_Install', 'plugins'));
PEEP::getRouter()->addRoute(new PEEP_Route('finish', 'install/security', 'INSTALL_CTRL_Install', 'finish'));

function install_tpl_feedback_flag($flag, $class = 'error')
{
    if ( INSTALL::getFeedback()->getFlag($flag) )
    {
        return $class;
    }
    
    return '';
}

function install_tpl_feedback()
{
    $feedBack = new INSTALL_CMP_FeedBack();
    
    return $feedBack->render();
}
