<?php

class MAILBOX_CTRL_Mailbox extends PEEP_ActionController
{
    /**
     * @var string
     */
    public $responderUrl;

    /**
     * @see PEEP_ActionController::init()
     *
     */
    public function init()
    {
        parent::init();

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('mailbox', 'mailbox'));
        $this->setPageHeadingIconClass('peep_ic_mail');
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->responderUrl = PEEP::getRouter()->urlFor("MAILBOX_CTRL_Mailbox", "responder");
    }

    /**
     * Action for mailbox ajax responder
     */
    public function responder()
    {
        if ( empty($_POST["function_"]) || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $function = (string) $_POST["function_"];

        $responder = new MAILBOX_CLASS_Responder();
        $result = call_user_func(array($responder, $function), $_POST);

        echo json_encode(array('result' => $result, 'error' => $responder->error, 'notice' => $responder->notice));
        exit();
    }

    public function users( $params )
    {
        header('Content-Type: text/plain');

        if (!PEEP::getUser()->isAuthenticated())
        {
            exit( json_encode(array()) );
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $data = $conversationService->getUserList(PEEP::getUser()->getId());

        exit( base64_encode(json_encode($data['list'])) );
    }

    public function convs( $params )
    {
        header('Content-Type: text/plain');

        if (!PEEP::getUser()->isAuthenticated())
        {
            exit( json_encode(array()) );
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $data = $conversationService->getConversationListByUserId(PEEP::getUser()->getId());

        exit( base64_encode(json_encode($data)) );
    }

    public function testapi($params)
    {
        $commands = array(
            array(
                'name'=>'mailbox_api_ping',
                'params'=>array(
                    'lastRequestTimestamp'=>0
                )
            )
        );

        $commandsResult = array();
        foreach ($commands as $command)
        {
//            pv($command);
            $event = new PEEP_Event('base.ping' . '.' . trim($command["name"]), $command["params"]);
            PEEP::getEventManager()->trigger($event);

            $event = new PEEP_Event('base.ping', array(
                "command" => $command["name"],
                "params" => $command["params"]
            ), $event->getData());
            PEEP::getEventManager()->trigger($event);

            $commandsResult[] = array(
                'name' => $command["name"],
                'data' => $event->getData()
            );
        }

//        pv($commandsResult);

        exit('end');
    }
}