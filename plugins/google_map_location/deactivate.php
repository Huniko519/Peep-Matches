<?php


$cache = array( 'question' => null, 'accountTypes' => array() );

$question = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

if ( !empty($question) )
{
    /*$question->accountTypeName = 'none';
    $question->onEdit = 0;
    $question->onJoin = 0;
    $question->onSearch = 0;
    $question->onView = 0;

    BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);*/
    
    $cache['question'] = get_object_vars($question);
    
    BOL_QuestionDao::getInstance()->deleteById($question->id);
}

$accountTypeToQuestionDtoList = BOL_QuestionService::getInstance()->findAccountTypeListByQuestionName('googlemap_location');

$accountTypeList = array();

foreach ( $accountTypeToQuestionDtoList as $accountTypeToQuestionDtoList )
{
    /* @var $accountTypeToQuestionDtoList BOL_QuestionToAccountType */
    $accountTypeList[$accountTypeToQuestionDtoList->accountType] = $accountTypeToQuestionDtoList->accountType;
}

$cache['accountTypes'] = $accountTypeList;

try
{
    PEEP::getConfig()->saveConfig('googlemap_location', 'cache', serialize($cache));
}
catch( Exception $ex )
{

}

BOL_ComponentAdminService::getInstance()->deleteWidget('GOOGLELOCATION_CMP_GroupsWidget');


