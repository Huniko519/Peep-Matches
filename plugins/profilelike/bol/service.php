<?php

class PROFILELIKE_BOL_Service
{
   private static $classInstance;
    
    /**
     * Returns class instance
     *
     * @return PROFILELIKE_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    private function __construct()
    {
        $this->profilelikeDao = PROFILELIKE_BOL_ProfilelikeDao::getInstance();
    }
	
	public function getTableName()
    {
        return PEEP_DB_PREFIX . 'profilelike';
    }  
}