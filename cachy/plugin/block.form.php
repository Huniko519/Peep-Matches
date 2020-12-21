<?php

function smarty_block_form( $params, $content )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty form name!');
    }

    $vr = PEEP_ViewRenderer::getInstance();
    
    $assignedForms = $vr->getAssignedVar('_peepForms_');
    
    if ( !isset($assignedForms[$params['name']]) )
    {
        throw new InvalidArgumentException('There is no form with name `' . $params['name'] . '` !');
    }

    // mark active form
    if ( $content === null )
    {
        $vr->assignVar('_peepActiveForm_', $assignedForms[$params['name']]);
        return;
    }

    /* @var $form PEEP_Form */
    $form = $vr->getAssignedVar('_peepActiveForm_');

    if ( isset($params['decorator']) )
    {
        $viewRenderer = PEEP_ViewRenderer::getInstance();
        $viewRenderer->assignVar('formInfo', $form->getElementsInfo());
        $content = $viewRenderer->renderTemplate(PEEP::getThemeManager()->getDecorator($params['decorator']));
    }

    unset($params['decorator']);
    unset($params['name']);
    return $form->render($content, $params);
}