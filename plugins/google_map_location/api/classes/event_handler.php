<?php

class GOOGLELOCATION_ACLASS_EventHandler
{
    public function __construct()
    {

    }

    public function questionsFieldGetValue( PEEP_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !empty($params['fieldName']) && $params['fieldName'] == 'googlemap_location' && !empty($params['value']) )
        {
            $location = $params['value'];

            if ( !empty($location['json']) )
            {
                $locationDto = new GOOGLELOCATION_BOL_Location();

                $locationDto->entityId = (int) $params['userId'];
                $locationDto->address = !empty($location['address']) ? $location['address'] : "";
                $locationDto->lat = (float) $location['latitude'];
                $locationDto->lng = (float) $location['longitude'];
                $locationDto->northEastLat = (float) $location['northEastLat'];
                $locationDto->northEastLng = (float) $location['northEastLng'];
                $locationDto->southWestLat = (float) $location['southWestLat'];
                $locationDto->southWestLng = (float) $location['southWestLng'];
                $locationDto->json = !empty($location['json']) ? $location['json'] : "";
                $locationDto->entityType = GOOGLELOCATION_BOL_LocationDao::ENTITY_TYPE_USER;

                $location = get_object_vars( $locationDto );

                $data = $location['address'];
            }
        }

        $e->setData($data);
    }

    public function init()
    {
        $handler = new GOOGLELOCATION_CLASS_EventHandler();
        $handler->genericInit();

        PEEP::getEventManager()->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
    }
}