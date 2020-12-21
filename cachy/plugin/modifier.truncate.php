<?php

function smarty_modifier_truncate( $string, $length, $ending = null )
{
    return UTIL_String::truncate($string, $length, $ending);
}