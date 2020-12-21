<?php

function smarty_block_block_decorator( $params, $content )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty decorator name!');
    }

    if ( $content === null )
    {
        return;
    }

    return PEEP::getThemeManager()->processBlockDecorator($params['name'], $params, $content);
}