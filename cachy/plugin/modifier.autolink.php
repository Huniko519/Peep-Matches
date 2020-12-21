<?php

function smarty_modifier_autolink( $string )
{
    return UTIL_HtmlTag::autoLink($string);
}