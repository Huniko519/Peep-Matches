<?php

abstract class PEEP_LogWriter
{
    abstract function processEntries( array $entries );
}