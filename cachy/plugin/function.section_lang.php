<?php

/**
 * 
 * @param array $params
 * @param PEEP_Smarty $smarty
 *
 * @return string
 *
 * {section_lang name="question section name"}
 *
 */
function smarty_function_section_lang( $params, $smarty )
{
    return BOL_QuestionService::getInstance()->getSectionLang(trim($params['name']));
}
?>
