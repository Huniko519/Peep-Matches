<?php


$dbo = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

if ( empty($dbo) )
{
    $cache = PEEP::getConfig()->getValue('googlemap_location', 'cache');
    $cache = unserialize($cache);
    
    $dbo = new BOL_Question();

    if ( !empty($cache['question']) )
    {
        $question = $cache['question'];

        $dbo->name = $question['name'];
        $dbo->sectionName = $question['sectionName'];
        $dbo->accountTypeName = $question['accountTypeName'];
        $dbo->type = $question['type'];
        $dbo->presentation = $question['presentation'];
        $dbo->required = $question['required'];
        $dbo->onJoin = $question['onJoin'];
        $dbo->onEdit = $question['onEdit'];
        $dbo->onSearch = $question['onSearch'];
        $dbo->onView = $question['onView'];
        $dbo->base = $question['base'];
        $dbo->removable = $question['removable'];
        $dbo->sortOrder = $question['sortOrder'];
        $dbo->columnCount = $question['columnCount'];
        $dbo->custom = $question['custom'];
    }
    else
    {
        $dbo = new BOL_Question();
        $dbo->accountTypeName = '';
        $dbo->removable = 0;
        $dbo->presentation = BOL_QuestionService::QUESTION_PRESENTATION_TEXT;
        $dbo->type = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
        $dbo->required = 1;
        $dbo->onEdit = 1;
        $dbo->onJoin = 1;
        $dbo->onSearch = 1;
        $dbo->onView = 1;
        $dbo->sectionName = 'location';
        $dbo->name = 'googlemap_location';
        $dbo->sortOrder = 0;
    }
    
    BOL_QuestionService::getInstance()->saveOrUpdateQuestion($dbo);

    $list = array();
    
    $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();
    
    if ( !empty($cache['accountTypes']) )
    {
        foreach( $accountTypeList as $accauntType )
        {
            /* @var $accauntType BOL_QuestionAccountType */
            if ( $cache['accountTypes'][$accauntType] == $accauntType  )
            {
                $list[$accauntType->name] = $accauntType->name;
            }
        }
    }
    
    if ( empty($list) )
    {
        foreach( $accountTypeList as $accauntType )
        {
            /* @var $accauntType BOL_QuestionAccountType */
            $list[$accauntType->name] = $accauntType->name;
        }
    }
    
    BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList(array('googlemap_location'), $list);
}

/* $event = new BASE_CLASS_EventCollector('ads.enabled_plugins');
PEEP::getEventManager()->trigger($event);

$pluginList = $event->getData(); */

$widgetService = BOL_ComponentAdminService::getInstance();

$widget = $widgetService->addWidget('GOOGLELOCATION_CMP_GroupsWidget', false);
$placeWidget = $widgetService->addWidgetToPlace($widget, 'group');
