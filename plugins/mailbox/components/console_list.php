<?php

class MAILBOX_CMP_ConsoleList extends PEEP_Component
{
    protected $viewAll = null, $itemKey, $listRsp;


    public function __construct( $consoleItemKey )
    {
        parent::__construct();

        $this->itemKey = $consoleItemKey;
        $this->listRsp = PEEP::getRouter()->urlFor('BASE_CTRL_Console', 'listRsp');
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::composeJsString('$.extend(PEEP.Console.getItem({$key}), PEEP_ConsoleList).construct({$params});', array(
            'key' => $this->itemKey,
            'params' => array(
                'rsp' => $this->listRsp,
                'key' => $this->itemKey
            )
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $viewAllUrl = PEEP::getRouter()->urlForRoute('mailbox_messages_default');
        $this->assign('viewAllUrl', $viewAllUrl);

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
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
                $script = '$("#mailboxConsoleListSendMessageBtn").click(function(){
                    PEEP.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                });';
                PEEP::getDocument()->addOnloadScript($script);
                $isAuthorizedSendMessage = true; //this service is promoted
            }
        }
        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);
    }
}