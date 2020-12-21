<?php

/**
 * 
 * @param array $params
 * @param PEEP_Smarty $smarty
 *
 * @return string
 *
 * {question_value_lang name="question name" value="value"}
 */
function smarty_function_question_value_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getQuestionValueLang(trim($params['name']), trim($params['value']));
}
?>
