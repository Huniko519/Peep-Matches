<?php

$section =  new BOL_QuestionSection();
$section->name = 'location';
$section->sortOrder = BOL_QuestionService::getInstance()->findLastSectionOrder() + 1;

BOL_QuestionService::getInstance()->saveOrUpdateSection($section);

$question = new BOL_Question();
$question->removable = 0;
$question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_TEXT;
$question->type = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
$question->onEdit = 1;
$question->onJoin = 1;
$question->onSearch = 1;
$question->onView = 1;
$question->sectionName = 'location';
$question->name = 'googlemap_location';
$question->sortOrder = 0;

$build = PEEP::getConfig()->getValue('base', 'soft_build');

if ( $build >= 7157 )
{
    $question->parent = '';
}

BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);

// -- add location question to all account types
$accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

$list = array();

foreach( $accountTypeList as $accauntType )
{
    /* @var $accauntType BOL_QuestionAccountType */
    $list[$accauntType->name] = $accauntType->name;
}

BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList(array('googlemap_location'), $list);

// ----------------------------------------------

$sectionLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_SECTION, $section->name);
$questionLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_LABEL, $question->name);
$descriptionLang = BOL_QuestionService::getInstance()->getQuestionLangKeyName(BOL_QuestionService::LANG_KEY_TYPE_QUESTION_DESCRIPTION, $question->name);

$defaultLanguage = BOL_LanguageService::getInstance()->findByTag('en');

if ( !empty($defaultLanguage) )
{
    try
    {
        BOL_LanguageService::getInstance()->addValue($defaultLanguage->id, 'base', $sectionLang, 'Location');
    }
    catch( Exception $ex )
    {

    }

    try
    {
         BOL_LanguageService::getInstance()->addValue($defaultLanguage->id, 'base', $questionLang, 'Location');
    }
    catch( Exception $ex )
    {

    }

    try
    {
         BOL_LanguageService::getInstance()->addValue($defaultLanguage->id, 'base', $descriptionLang, '');
    }
    catch( Exception $ex )
    {

    }
}

$sql = "CREATE TABLE IF NOT EXISTS `" . PEEP_DB_PREFIX . "googlelocation_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `entityType` ENUM( 'user', 'event' ) NOT NULL,
  `countryCode` varchar(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `lat` DECIMAL( 15, 4 ) NOT NULL,
  `lng` DECIMAL( 15, 4 ) NOT NULL,
  `northEastLat` DECIMAL( 15, 4 ) NOT NULL,
  `northEastLng` DECIMAL( 15, 4 ) NOT NULL,
  `southWestLat` DECIMAL( 15, 4 ) NOT NULL,
  `southWestLng` DECIMAL( 15, 4 ) NOT NULL,
  `json` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `entityId` (`entityId`, `entityType`),
  KEY `lan_lng` (`lat`,`lng`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

PEEP::getDbo()->query($sql);

PEEP::getPluginManager()->addPluginSettingsRouteName('googlelocation', 'googlelocation_admin');


if ( !PEEP::getConfig()->configExists('googlelocation', 'api_key') )
{
    PEEP::getConfig()->addConfig('googlelocation', 'api_key', '', 'API key');
}

if ( !PEEP::getConfig()->configExists('googlemap_location', 'cache') )
{
    PEEP::getConfig()->addConfig('googlemap_location', 'cache', '');
}

if ( !PEEP::getConfig()->configExists('googlelocation', 'distance_units') )
{
    PEEP::getConfig()->addConfig('googlelocation', 'distance_units', 'miles', 'Distance Units');
}

if ( !PEEP::getConfig()->configExists('googlelocation', 'auto_fill_location_on_search') )
{
    PEEP::getConfig()->addConfig('googlelocation', 'auto_fill_location_on_search', '0', 'Auto fill location on search');
}

if ( !PEEP::getConfig()->configExists('googlelocation', 'country_restriction') )
{
    PEEP::getConfig()->addConfig('googlelocation', 'country_restriction', '');
}

PEEP::getLanguage()->importPluginLangs(PEEP::getPluginManager()->getPlugin('googlelocation')->getRootDir() . 'langs.zip', 'googlelocation');
