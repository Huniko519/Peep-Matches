<?php

$plugin = PEEP::getPluginManager()->getPlugin('pcgallery');

$preference = BOL_PreferenceService::getInstance()->findPreference("pcgallery_source");

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'pcgallery_source';
$preference->sectionName = 'general';
$preference->defaultValue = "all";
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

$preference = BOL_PreferenceService::getInstance()->findPreference('pcgallery_album');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'pcgallery_album';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', $plugin->getKey());