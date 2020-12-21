<?php

function printVar( $var, $exit = false )
{
    UTIL_Debug::varDump($var, $exit);
}

function pv( $var, $exit = false )
{
    UTIL_Debug::varDump($var, $exit);
}

function pve( $var, $exit = false )
{
    UTIL_Debug::varDump($var, $exit);
    exit;
}

function profiler_mark( $markKey = null )
{
    UTIL_Profiler::getInstance()->mark($markKey);
}