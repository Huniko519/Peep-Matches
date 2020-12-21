<?php

class MAILBOX_CTRL_Messages extends PEEP_ActionController
{
    public function index( $params )
    {
        if (!PEEP::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }

        $this->setPageHeading(PEEP::getLanguage()->text('mailbox', 'page_heading_messages'));

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $listParams = array();
        if (!empty($params['convId']))
        {
            $listParams['conversationId'] = $params['convId'];

            $conversation = $conversationService->getConversation($params['convId']);
            if (empty($conversation))
            {
                throw new Redirect404Exception();
            }

            /*$conversationMode = $conversationService->getConversationMode($params['convId']);
            if ($conversationMode != 'mail')
            {
                throw new Redirect404Exception();
            }*/
        }

        $listParams['activeModeList'] = $conversationService->getActiveModeList();

        //Conversation list
        $conversationList = new MAILBOX_CMP_ConversationList($listParams);
        $this->addComponent('conversationList', $conversationList);

        $conversationContainer = new MAILBOX_CMP_Conversation();
        $this->addComponent('conversationContainer', $conversationContainer);

        $activeModeList = $conversationService->getActiveModeList();
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $this->assign('mailModeEnabled', $mailModeEnabled);

        $actionName = 'send_message';

        $event = new PEEP_Event('mailbox.show_send_message_button', array(), false);
        PEEP::getEventManager()->trigger($event);
        $showSendMessage = $event->getData();

        $isAuthorizedSendMessage = $showSendMessage && PEEP::getUser()->isAuthorized('mailbox', $actionName);
        if (!$isAuthorizedSendMessage)
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $script = '$("#newMessageBtn").click(function(){
                    PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                });';
                PEEP::getDocument()->addOnloadScript($script);
                $isAuthorizedSendMessage = true; //the service is promoted
            }
        }

        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);

        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        $this->assign('chatModeEnabled', $chatModeEnabled);

    }

    public function chatConversation( $params ){
        $this->redirect(PEEP::getRouter()->urlForRoute('mailbox_messages_default'));
    }

    public function conversation($params)
    {
//        pv($_REQUEST);

        exit('1');
    }

    public function conversations($params)
    {
        if (!PEEP::getUser()->isAuthenticated())
        {
            exit(array());
        }

        $userId = PEEP::getUser()->getId();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            $list = $conversationService->getConversationListByUserId($userId);
            exit(json_encode($list));
        }
        else
        {
            exit(json_encode('todo'));
        }
    }
}