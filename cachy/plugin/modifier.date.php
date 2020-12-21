<?php

function smarty_modifier_date( $timeStamp, $dateOnly = false )
{
    return UTIL_DateTime::formatDate($timeStamp, $dateOnly);
}