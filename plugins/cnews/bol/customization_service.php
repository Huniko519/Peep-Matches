<?php

class CNEWS_BOL_CustomizationService
{
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return CNEWS_BOL_CustomizationService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $filters = array();
    
    private function __construct()
    {
        
    }
    
    public function getActionTypes()
    {
        $event = new BASE_CLASS_EventCollector('feed.collect_configurable_activity');
        PEEP::getEventManager()->trigger($event);
        $actions = array();
        $eventData = $event->getData();
        
        $configTypes = json_decode(PEEP::getConfig()->getValue('cnews', 'disabled_action_types'), true);
        
        foreach ( $eventData as $item )
        {
            $item['activity'] = is_array($item['activity']) ? implode(',', $item['activity']) : $item['activity'];
            
            $item['active'] = !isset($configTypes[$item['activity']]) ? empty($item['active']) || $item['active'] : $configTypes[$item['activity']];
            $actions[] = $item;
        }
        
        return $actions; 
    }
    
    public function getDisabledEntityTypes()
    {
        $allTypes = $this->getActionTypes();
        $out = array();
        foreach ( $allTypes as $type )
        {
            if ( !$type['active'] )
            {
                $out[] = $type['activity'];
            }
        }
        
        return $out;
    }
}