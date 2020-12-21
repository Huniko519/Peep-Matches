<?php

class BASE_CTRL_Ping extends PEEP_ActionController
{
    const PING_EVENT = 'base.ping';

    public function index()
    {
        $request = json_decode($_POST['request'], true);
        $stack = $request['stack'];

        $responseStack = array();

        foreach ( $stack as $c )
        {
            $event = new PEEP_Event(self::PING_EVENT . '.' . trim($c['command']), $c['params']);
            PEEP::getEventManager()->trigger($event);

            $event = new PEEP_Event(self::PING_EVENT, $c, $event->getData());
            PEEP::getEventManager()->trigger($event);

            $responseStack[] = array(
                'command' => $c['command'],
                'result' => $event->getData()
            );
        }

        echo json_encode(array(
            'stack' => $responseStack
        ));

        exit;
    }
}