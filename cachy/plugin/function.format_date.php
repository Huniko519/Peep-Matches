<?php

function smarty_function_format_date( $params, $smarty )
{
    return UTIL_DateTime::formatDate($params['timestamp']);
}
?>