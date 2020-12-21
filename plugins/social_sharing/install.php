<?php

if ( !PEEP::getConfig()->configExists('socialsharing', 'api_key') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'api_key', '');
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'order') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'order', '');
}

// sharing servises

if ( !PEEP::getConfig()->configExists('socialsharing', 'facebook') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'facebook', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'twitter') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'twitter', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'googlePlus') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'googlePlus', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'pinterest') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'pinterest', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'linkedin') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'linkedin', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'digg') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'digg', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'delicious') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'delicious', 1);
}

if ( !PEEP::getConfig()->configExists('socialsharing', 'stumbleupon') )
{
    PEEP::getConfig()->addConfig('socialsharing', 'stumbleupon', 1);
}




PEEP::getPluginManager()->addPluginSettingsRouteName('socialsharing', 'socialsharing.admin');

BOL_LanguageService::getInstance()->importPrefixFromZip(PEEP_DIR_PLUGIN . 'social_sharing' . DS . 'langs.zip', 'socialsharing');

$image = new UTIL_Image(PEEP::getPluginManager()->getPlugin('socialsharing')->getRootDir() . 'logo' . DS . 'logo.jpg');
$imagePath = PEEP::getPluginManager()->getPlugin('socialsharing')->getUserFilesDir().'logo.jpg';

$width = $image->getWidth();
$height = $image->getHeight();

$side = $width >= $height ? $height : $width;
$side = $side > 200 ? 200 : $side;

$image->resizeImage($side, $side, true)->saveImage($imagePath);
