<?php

class CNEWS_CLASS_FormatManager
{
    const FORMAT_EMPTY = "empty";
    
    /**
     * Singleton instance.
     *
     * @var CNEWS_CLASS_FormatManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_CLASS_FormatManager
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $formats = array();
    
    /**
     *
     * @var PEEP_Plugin
     */
    private $plugin;
    
    private function __construct()
    {
        $this->plugin = PEEP::getPluginManager()->getPlugin("cnews");
    }
    
    public function getFormatNames() 
    {
        return array_keys($this->formats);
    }
    
    public function renderFormat( $name, $vars )
    {
        $beforeRenderEvent = new PEEP_Event("feed.before_render_format", array(
            "format" => $name,
            "vars" => $vars
        ), $vars);
        PEEP::getEventManager()->trigger($beforeRenderEvent);

        $renderEvent = new PEEP_Event("feed.render_format", array(
            "format" => $name,
            "vars" => $beforeRenderEvent->getData()
        ), null);
        PEEP::getEventManager()->trigger($renderEvent);
        
        $rendered = $renderEvent->getData();
        
        if ( $rendered === null )
        {
            if ( empty($this->formats[$name]) )
            {
                throw new InvalidArgumentException("Undefined Cnews format `$name`");
            }
            
            $formatClass = $this->formats[$name];
            
            /* @var $formatObject CNEWS_CLASS_Format */
            $formatObject = new $formatClass($vars, $name);
            $rendered = $formatObject->render();
        }
        
        $afterRenderEvent = new PEEP_Event("feed.after_render_format", array(
            "format" => $name,
            "vars" => $vars
        ), $rendered);
        PEEP::getEventManager()->trigger($afterRenderEvent);
        
        return $afterRenderEvent->getData();
    }

    public function addFormat($name, $className)
    {
        $this->formats[$name] = $className;
    }
    
    public function collectFormats()
    {
        $event = new BASE_CLASS_EventCollector("feed.collect_formats");
        PEEP::getEventManager()->trigger($event);
        
        foreach ( $event->getData() as $format )
        {
            $this->addFormat($format["name"], $format["class"]);
        }
    }
    
    public function init()
    {
        PEEP::getAutoloader()->addPackagePointer("CNEWS_FORMAT", $this->plugin->getRootDir() . "formats" . DS);
        PEEP::getAutoloader()->addPackagePointer("CNEWS_MFORMAT", $this->plugin->getMobileDir() . "formats" . DS);
        
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_PLUGINS_INIT, array($this, "collectFormats"));
    }
}