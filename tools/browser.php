<?php

require_once PEEP_DIR_LIB . 'browser' . DS . 'browser.php';

class UTIL_Browser
{
    public static function isSmartphone()
    {
        require_once PEEP_DIR_LIB . 'mobileesp' . DS . 'mdetect.php';
        $obj = new uagent_info();
        return (bool) $obj->DetectSmartphone();
    }

    /**
     * @param string $agentString
     * @return boolean
     */
    public static function isMobile( $agentString )
    {
        return self::getBrowserObj($agentString)->isMobile();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getBrowser( $agentString )
    {
        return self::getBrowserObj($agentString)->getBrowser();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getVersion( $agentString )
    {
        return self::getBrowserObj($agentString)->getVersion();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getPlatform( $agentString )
    {
        return self::getBrowserObj($agentString)->getPlatform();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function isRobot( $agentString )
    {
        return self::getBrowserObj($agentString)->isRobot();
    }

    /**
     * @param string $agentString
     * @return CSBrowser
     */
    private static function getBrowserObj( $agentString )
    {
        return new CSBrowser($agentString);
    }

    private static function getWurfl()
    {
        
    }
}
