<?php

/**
 *
 * @param array $params
 * @param PEEP_Smarty $smarty
 *
 * @return string
 *
 * {question_description_lang name="question name"}
 */
function smarty_function_question_description_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getQuestionDescriptionLang(trim($params['name']));
}
?>
