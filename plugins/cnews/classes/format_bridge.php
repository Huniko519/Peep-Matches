<?php

class CNEWS_CLASS_FormatBridge
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_CLASS_FormatBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_CLASS_FormatBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var CNEWS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = CNEWS_BOL_Service::getInstance();
    }
    
    public function beforeRenderFormat( PEEP_Event $event )
    {
        
    }
    
    public function renderFormat( PEEP_Event $event )
    {
        
    }
    
    public function afterRenderFormat( PEEP_Event $event )
    {
        
    }
    
    public function collectFormats( BASE_CLASS_EventCollector $event )
    {
        
    }
    
    public function init()
    {
        PEEP::getEventManager()->bind("feed.collect_formats", array($this, "collectFormats"));
        PEEP::getEventManager()->bind("feed.before_render_format", array($this, "beforeRenderFormat"));
        PEEP::getEventManager()->bind("feed.render_format", array($this, "renderFormat"));
        PEEP::getEventManager()->bind("feed.after_render_format", array($this, "afterRenderFormat"));
    }
}