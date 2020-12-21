<?php

function smarty_block_style( $params, $styles, $smarty )
{
    if ( $styles === null )
    {
        return;
    }

    PEEP::getDocument()->addStyleDeclaration($styles);
}