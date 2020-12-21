<?php

function smarty_block_script( $params, $script, $smarty )
{
    if ( $script === null )
    {
        return;
    }

    $document = PEEP::getDocument();

    if ( $document === null )
    {
        return;
    }

    $document->addOnloadScript($script);
}