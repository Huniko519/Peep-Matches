<?php

function smarty_function_print_var( $params, $smarty )
{
    $isEcho = ((isset($params['echo'])) && $params['echo'] === true);
    printVar($params['var'], $isEcho);
}
?>