<?php

class PEEP_Language
{
    /**
     * @var PEEP_EventManager
     */
    private $eventManager;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->eventManager = PEEP::getEventManager();
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Language
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Language
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function text( $prefix, $key, array $vars = null )
    {
        if ( empty($prefix) || empty($key) )
        {
            return $prefix . '+' . $key;
        }

        $text = null;
        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key);
        }
        catch ( Exception $e )
        {
            return $prefix . '+' . $key;
        }

        if ( $text === null )
        {
            return $prefix . '+' . $key;
        }

        $event = new PEEP_Event("core.get_text", array("prefix" => $prefix, "key" => $key, "vars" => $vars));
        $this->eventManager->trigger($event);

        if ( $event->getData() !== null )
        {
            return $event->getData();
        }

        $text = UTIL_String::replaceVars($text, $vars);

        return $text;
    }

    public function valueExist( $prefix, $key )
    {
        if ( empty($prefix) || empty($key) )
        {
            throw new InvalidArgumentException('Invalid parameter $prefix or $key');
        }

        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key);
        }
        catch ( Exception $e )
        {
            return false;
        }

        if ( $text === null )
        {
            return false;
        }

        return true;
    }

    public function addKeyForJs( $prefix, $key )
    {
        $text = json_encode($this->text($prefix, $key));

        PEEP::getDocument()->addOnloadScript("PEEP.registerLanguageKey('$prefix', '$key', $text);", -99);
    }

    public function getCurrentId()
    {
        return BOL_LanguageService::getInstance()->getCurrent()->getId();
    }

    public function importPluginLangs( $path, $key, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromZip($path, $key, $refreshCache, $addLanguage);
    }
}
