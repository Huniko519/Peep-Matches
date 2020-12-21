<?php

class GOOGLELOCATION_CTRL_EventMap extends PEEP_ActionController
{
    const MAX_EVENT_COUNT = 16;
    
    private function getEventMapCmp($backUri = null)
    {
        if( !PEEP::getPluginManager()->isPluginActive('event') )
        {
            throw new Redirect404Exception();
        }
        
        $map = new GOOGLELOCATION_CMP_Map();
        $map->setHeight('600px');
        $map->setZoom(2);
        $map->setCenter(30,10);
        $map->setMapOption('scrollwheel', 'false');

        $locationList = GOOGLELOCATION_BOL_LocationService::getInstance()->getAllLocationsForEntityType('event');

        $entityIdList = array();
        $entityLocationList = array();

        foreach( $locationList as $location )
        {
            $entityIdList[$location['entityId']] = $location['entityId'];
            $entityLocationList[$location['entityId']] = $location;
        }
        $locationList = $entityLocationList;
        
        $eventsList = EVENT_BOL_EventService::getInstance()->findPublicEvents(null, 1000);
        $publicEventsId = array();
        $tmpEventList = array();
        
        foreach( $eventsList as $event )
        {
            $publicEventsId[$event->id] = $event->id;
            $tmpEventList[$event->id] = $event;
        }
        $eventsList = $tmpEventList;
        
        $entityIdList = array_intersect($entityIdList, $publicEventsId);
         
        $publicLocationList = array();
        $publicEventList = array();
        
        foreach( $entityIdList as $entityId )
        {
            $publicLocationList[$entityId] = $locationList[$entityId];
            $publicEventList[$entityId] = $eventsList[$entityId];
        }
        
        $events = EVENT_BOL_EventService::getInstance()->getListingDataWithToolbar($publicEventList);
                
        $pointList = GOOGLELOCATION_BOL_LocationService::getInstance()->getPointList($publicLocationList);
        
        foreach( $pointList as $point )
        {
            if( !empty( $point['entityIdList'] ) )
            {
                $content = "";
                
                if ( $point['count'] > 1 ) 
                {
                    $listCmp = new GOOGLELOCATION_CMP_MapEventList($point['entityIdList'], $point['location']['lat'], $point['location']['lng'], $backUri);
                    $content .= $listCmp->render();
                    unset($listCmp);
                }
                else 
                {
                    $eventId = current($point['entityIdList']);
                    
                    if( !empty($events[$eventId]) )
                    {
                        $cmp = new GOOGLELOCATION_CMP_MapItem();
                        $cmp->setAvatar(array('src' => $events[$eventId]['imageSrc'] ));
                        $content = "<a href='{$events[$eventId]['eventUrl']}'>".$events[$eventId]['title']."</a>
                            <div>{$events[$eventId]['content']}</div>
                            <div>{$publicLocationList[$eventId]['address']}</div> ";
                        $cmp->setContent($content);

                        $content = $cmp->render();
                    }
                }
                
                if ( !empty($content) )
                {
                    $map->addPoint($point['location'], '', $content);
                }
            }
        }
        
        return $map;
    }
    
    public function map()
    {
        $event = new PEEP_Event('event.is_plugin_active');
        PEEP::getEventManager()->trigger($event);
        
        $data = $event->getData();
        
        if ( !$data )
        {
            throw new Redirect404Exception();
        }
        
        $event = new PEEP_Event('event.get_content_menu');
        PEEP::getEventManager()->trigger($event);
        
        $menu = $event->getData();
        
        $menu = EVENT_BOL_EventService::getInstance()->getContentMenu();
        $menu->getElement('events_map')->setActive(true);
        $this->addComponent('menu', $menu);

        $language = PEEP::getLanguage();
        $this->setPageHeading($language->text('googlelocation', 'map_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_bookmark');
        
        $this->addComponent("map", $this->getEventMapCmp(PEEP::getRouter()->getUri()));
        
        PEEP::getEventManager()->trigger(new PEEP_Event('googlelocation.add_js_lib'));
    }
}
