<?php

function smarty_function_desc( $params )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty input name!');
    }
    
    $vr = PEEP_ViewRenderer::getInstance();
    
    /* @var $form Form */
    $form = $vr->getAssignedVar('_peepActiveForm_');

    if ( !$form )
    {
        throw new InvalidArgumentException('There is no form for input `' . $params['name'] . '` !');
    }

    $input = $form->getElement(trim($params['name']));

    if ( $input === null )
    {
        throw new LogicException('No input named `' . $params['name'] . '` in form !');
    }

    return $input->getDescription();
}