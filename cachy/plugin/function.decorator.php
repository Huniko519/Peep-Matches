<?php

function smarty_function_decorator( $params )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty decorator name!');
    }

    return PEEP::getThemeManager()->processDecorator($params['name'], $params);
}
