<?php

function smarty_modifier_simple_date( $timeStamp, $dateOnly = false )
{
    return UTIL_DateTime::formatSimpleDate($timeStamp, $dateOnly);
}