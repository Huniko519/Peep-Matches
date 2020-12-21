<?php

class BASE_CLASS_DbLogWriter extends PEEP_LogWriter
{

    /**
     * Constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param array $entries
     */
    public function processEntries( array $entries )
    {
        BOL_LogService::getInstance()->addEntries($entries);
    }
}
