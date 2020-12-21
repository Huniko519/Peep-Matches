<?php


/**
 * @param array $params
 * @param PEEP_Smarty $smarty
 *
 * @return string
 *
 * {question_lang name="question name"}
 *
 */
function smarty_function_question_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getQuestionLang(trim($params['name']));
}
?>
