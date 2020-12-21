<?php

function smarty_function_component( $params, $smarty )
{

    if ( !isset($params['class']) || !mb_strstr($params['class'], '_') )
    {
        throw new InvalidArgumentException('Ivalid class name provided `'.$params['class'].'`');
    }

    $class = trim($params['class']);
    unset($params['class']);

    if ( !class_exists($class) )
    {
        return '';
    }

    $cmp = PEEP::getClassInstance($class, $params);
    
    return $cmp->render();
}