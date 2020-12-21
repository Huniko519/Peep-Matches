<?php

BOL_QuestionService::getInstance()->deleteSection('location');
$question = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');

if ( !empty($question) )
{
    BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
    BOL_QuestionService::getInstance()->deleteQuestionToAccountTypeByQuestionName('googlemap_location');
}

BOL_QuestionDataDao::getInstance()->deleteByQuestionNamesList(array('googlemap_location'));