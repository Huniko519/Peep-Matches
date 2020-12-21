<?php

class BASE_CTRL_ApiResponder extends PEEP_ActionController
{
    private function validateParams( $params, $requiredList )
    {
        $fails = array();

        foreach ( $requiredList as $required )
        {
            if ( empty($params[$required]) )
            {
                $fails[] = $required;
            }
        }

        if ( !empty($fails) )
        {
            throw new InvalidArgumentException('Next params are required: ' . implode(', ', $fails));
        }
    }

    public function triggerEvent($params)
    {
        throw new Exception('This method is deprecated');

        $this->validateParams($params, array('eventName'));

        $eventName = trim($params['eventName']);
        $eventParams = empty($params['params']) ? array() : $params['params'];
        $eventData = empty($params['data']) ? array() : $params['data'];

        $event = new PEEP_Event($eventName, $eventParams, $eventData);

        PEEP::getEventManager()->trigger($event);

        return $event->getData();
    }
}