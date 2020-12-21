<?php

class GOOGLEAUTH_BOL_AdminService extends GOOGLEAUTH_BOL_Service
{
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GOOGLEAUTH_BOL_AdminService
     */
    public static function getInstance() {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public static function configureApplication() {
   
        return true;
    }


}
