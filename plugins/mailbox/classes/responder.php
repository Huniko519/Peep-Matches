<?php

class MAILBOX_CLASS_Responder
{
    public $error;
    public $notice;

    /**
     * Class constructor
     */
    public function __construct()
    {
        return $this;
    }

    public function deleteConversation( $params )
    {
        if (!PEEP::getUser()->isAuthenticated())
        {
            echo json_encode(array());
            exit;
        }

        $userId = PEEP::getUser()->getId();

        $conversationId = (int) $params['conversationId'];

        if ( !empty($conversationId) )
        {
            MAILBOX_BOL_ConversationService::getInstance()->deleteConversation(array($conversationId), $userId);

            $this->notice = PEEP::getLanguage()->text('mailbox', 'delete_conversation_message');
            return true;
        }
        else
        {
            $this->error = PEEP::getLanguage()->text('mailbox', 'conversation_id_undefined');
            return false;
        }
    }
}