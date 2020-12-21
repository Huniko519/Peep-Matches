<?php

class MAILBOX_CLASS_Credits
{
    private $actions;
    private $authActions = array();

    public function __construct()
    {
        $mailboxEvent = new PEEP_Event('mailbox.admin.add_auth_labels');
        PEEP::getEventManager()->trigger($mailboxEvent);
        $data = $mailboxEvent->getData();
        if (!empty($data))
        {
            $actionLabels = $data['actions'];
            $actionNames = array_keys($actionLabels);
            foreach ($actionNames as $actionName)
            {
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => $actionName, 'amount' => 0);
                $this->authActions[$actionName] = $actionName;
            }
        }
        else
        {
            $activeModes = array('mail', 'chat');

            if (in_array('mail', $activeModes))
            {
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'send_message', 'amount' => 0);
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'read_message', 'amount' => 0);
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'reply_to_message', 'amount' => 0);

                $this->authActions['send_message'] = 'send_message';
                $this->authActions['read_message'] = 'read_message';
                $this->authActions['reply_to_message'] = 'reply_to_message';
            }

            if (in_array('chat', $activeModes))
            {
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'send_chat_message', 'amount' => 0);
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'read_chat_message', 'amount' => 0);
                $this->actions[] = array('pluginKey' => 'mailbox', 'action' => 'reply_to_chat_message', 'amount' => 0);

                $this->authActions['send_chat_message'] = 'send_chat_message';
                $this->authActions['read_chat_message'] = 'read_chat_message';
                $this->authActions['reply_to_chat_message'] = 'reply_to_chat_message';
            }
        }
    }

    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $e )
    {
        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }
    }

    public function triggerCreditActionsAdd()
    {
        $e = new BASE_CLASS_EventCollector('usercredits.action_add');

        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }

        PEEP::getEventManager()->trigger($e);
    }
}