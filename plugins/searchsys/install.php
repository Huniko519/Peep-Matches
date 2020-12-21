<?php
PEEP::getPluginManager()->addPluginSettingsRouteName('searchsys', 'searchsys.admin-config');

$config = PEEP::getConfig();

if ( !$config->configExists('searchsys', 'questions') )
{
    // set defaults for all account types
	$def = array('sex' => 1, 'relationship' => 1);
	 
	$sql = "SELECT `name` FROM `".PEEP_DB_PREFIX."base_question_account_type` ORDER BY `sortOrder` ASC";
	$accountTypes = PEEP::getDbo()->queryForColumnList($sql);
	
	$set = array();
	foreach ( $accountTypes as $type )
	{
	    $set[$type] = $def;
	}
	
	$config->addConfig('searchsys', 'questions', json_encode($set), 'Questions');
}

if ( !$config->configExists('searchsys', 'show_advanced') )
{
    $config->addConfig('searchsys', 'show_advanced', 1, 'Show link to advanced search');
}

if ( !$config->configExists('searchsys', 'show_section') )
{
    $config->addConfig('searchsys', 'show_section', 0, 'Show section name');
}

if ( !$config->configExists('searchsys', 'username_search') )
{
    $config->addConfig('searchsys', 'username_search', 0, 'Allow username search');
}

if ( !$config->configExists('searchsys', 'site_search_enabled') )
{
    $config->addConfig('searchsys', 'site_search_enabled', '1', 'Enable site search');
}

if ( !$config->configExists('searchsys', 'site_search_groups') )
{
    $config->addConfig('searchsys', 'site_search_groups', '[]', 'Site search groups');
}

if ( !$config->configExists('searchsys', 'online_only_enabled') )
{
    $config->addConfig('searchsys', 'online_only_enabled', '1', 'Add "online only" checkbox');
}

if ( !$config->configExists('searchsys', 'with_photo_enabled') )
{
    $config->addConfig('searchsys', 'with_photo_enabled', '1', 'Add "with photo" checkbox');
}

$authorization = PEEP::getAuthorization();
$groupName = 'searchsys';
$authorization->addGroup($groupName, false);
$authorization->addAction($groupName, 'search_system', true);
$authorization->addAction($groupName, 'site_search', true);

$path = PEEP::getPluginManager()->getPlugin('searchsys')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'searchsys');