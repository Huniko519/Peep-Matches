<?php

class BASE_CTRL_Invitation extends PEEP_ActionController
{
    public function ajax()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect403Exception();
        }

        $command = $_POST['command'];
        $data = json_decode($_POST['data'], true);

        $event = new PEEP_Event('invitations.on_command', array(
            'command' => $command,
            'data' => $data
        ));

        PEEP::getEventManager()->trigger($event);
        $result = $event->getData();

        echo json_encode(array(
            'script' => (string) $result
        ));

        exit;
    }
}