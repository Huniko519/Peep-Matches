<?php

class MAILBOX_CMP_Conversation extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $this->assign('defaultAvatarUrl', $defaultAvatarUrl);

        $js = "PEEP.Mailbox.conversationController = new MAILBOX_ConversationView();";

        PEEP::getDocument()->addOnloadScript($js, 3006);

        //TODO check this config
        $enableAttachments = PEEP::getConfig()->getValue('mailbox', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        $replyToMessageActionPromotedText = '';
        $isAuthorizedReplyToMessage = PEEP::getUser()->isAuthorized('mailbox', 'reply_to_message');
        $isAuthorizedReplyToMessage = $isAuthorizedReplyToMessage || PEEP::getUser()->isAuthorized('mailbox', 'send_chat_message');
        if (!$isAuthorizedReplyToMessage)
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'reply_to_message');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $replyToMessageActionPromotedText = $status['msg'];
            }
        }
        $this->assign('isAuthorizedReplyToMessage', $isAuthorizedReplyToMessage);

        $isAuthorizedReplyToChatMessage = PEEP::getUser()->isAuthorized('mailbox', 'reply_to_chat_message');
        if (!$isAuthorizedReplyToChatMessage)
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', 'reply_to_chat_message');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $replyToMessageActionPromotedText = $status['msg'];
            }
        }
        $this->assign('isAuthorizedReplyToChatMessage', $isAuthorizedReplyToChatMessage);

        $this->assign('replyToMessageActionPromotedText', $replyToMessageActionPromotedText);

        if ( $isAuthorizedReplyToMessage )
        {
            $text = new WysiwygTextarea('mailbox_message');
            $text->setId('conversationTextarea');
            $this->assign('mailbox_message', $text->renderInput());
        }

        return parent::render();
    }
}