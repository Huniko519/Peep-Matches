<?php

function smarty_function_submit( $params )
{
    $vr = PEEP_ViewRenderer::getInstance();

    /* @var $form Form */
    $form = $vr->getAssignedVar('_peepActiveForm_');

    if ( !$form )
    {
        throw new InvalidArgumentException('Cant find form for input `' . $params['name'] . '` !');
    }

    $name = $params['name'] ? trim($params['name']) : null;

    $input = $form->getSubmitElement($name);

    if ( $input === null )
    {
        throw new WarningException('No input named `' . $params['name'] . '` in form !');
    }

    return $input->renderInput($params);
}