<?php


PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('contactimporter')->getRootDir() . 'langs.zip', 'contactimporter');

PEEP::getPluginManager()->addPluginSettingsRouteName('contactimporter', 'contactimporter_admin');

if ( !PEEP::getConfig()->configExists('contactimporter', 'facebook_app_id') )
{
    PEEP::getConfig()->addConfig('contactimporter', 'facebook_app_id', '', '');
}

if ( !PEEP::getConfig()->configExists('contactimporter', 'facebook_app_secret') )
{
    PEEP::getConfig()->addConfig('contactimporter', 'facebook_app_secret', '', '');
}


if ( !PEEP::getConfig()->configExists('contactimporter', 'google_client_id') )
{
    PEEP::getConfig()->addConfig('contactimporter', 'google_client_id', '');
}

if ( !PEEP::getConfig()->configExists('contactimporter', 'google_client_secret') )
{
    PEEP::getConfig()->addConfig('contactimporter', 'google_client_secret', '');
}

