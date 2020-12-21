<?php

class MAILBOX_CLASS_NewMessageForm extends Form
{
    const DISPLAY_CAPTCHA_TIMEOUT = 20;

    public $displayCapcha = false;

    /**
     * Class constructor
     *
     */
    public function __construct(MAILBOX_CMP_NewMessage $component = null)
    {
        $language = PEEP::getLanguage();

        parent::__construct('mailbox-new-message-form');
        $this->setId('mailbox-new-message-form');
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction( PEEP::getRouter()->urlFor('MAILBOX_CTRL_Ajax', 'newMessage') );
        $this->setEmptyElementsErrorMessage('');

        $this->setEnctype('multipart/form-data');

        $subject = new TextField('subject');
//        $subject->setHasInvitation(true);
//        $subject->setInvitation($language->text('mailbox', 'subject'));
        $subject->addAttribute('placeholder', $language->text('mailbox', 'subject'));

        $requiredValidator = new RequiredValidator();
        $requiredValidator->setErrorMessage( $language->text('mailbox', 'subject_is_required') );
        $subject->addValidator($requiredValidator);

        $validatorSubject = new StringValidator(1, 2048);
        $validatorSubject->setErrorMessage($language->text('mailbox', 'message_too_long_error', array('maxLength' => 2048)));
        $subject->addValidator($validatorSubject);

        $this->addElement($subject);

        $validator = new StringValidator(1, MAILBOX_BOL_AjaxService::MAX_MESSAGE_TEXT_LENGTH);
        $validator->setErrorMessage($language->text('mailbox', 'message_too_long_error', array('maxLength' => MAILBOX_BOL_AjaxService::MAX_MESSAGE_TEXT_LENGTH)));

        $textarea = PEEP::getClassInstance("MAILBOX_CLASS_Textarea", "message");
        
        /* @var $textarea MAILBOX_CLASS_Textarea */
        $textarea->addValidator($validator);
        $textarea->setCustomBodyClass("mailbox");
//        $textarea->setHasInvitation(true);
//        $textarea->setInvitation($language->text('mailbox', 'message_invitation'));
        $textarea->addAttribute('placeholder', $language->text('mailbox', 'message_invitation'));
        $requiredValidator = new WyswygRequiredValidator();
        $requiredValidator->setErrorMessage( $language->text('mailbox', 'chat_message_empty') );
        $textarea->addValidator($requiredValidator);

        $this->addElement($textarea);

        $user = PEEP::getClassInstance("MAILBOX_CLASS_UserField", "opponentId");
        
        /* @var $user MAILBOX_CLASS_UserField */
//        $user->setHasInvitation(true);
//        $user->setInvitation($language->text('mailbox', 'to'));

        $requiredValidator = new RequiredValidator();
        $requiredValidator->setErrorMessage( $language->text('mailbox', 'recipient_is_required') );
        $user->addValidator($requiredValidator);

        $this->addElement($user);

        if (PEEP::getSession()->isKeySet('mailbox.new_message_form_attachments_uid'))
        {
            $uidValue = PEEP::getSession()->get('mailbox.new_message_form_attachments_uid');
        }
        else
        {
            $uidValue = UTIL_HtmlTag::generateAutoId('mailbox_new_message');
            PEEP::getSession()->set('mailbox.new_message_form_attachments_uid', $uidValue);
        }

        $uid = new HiddenField('uid');
        $uid->setValue($uidValue);
        $this->addElement($uid);

        $configs = PEEP::getConfig()->getValues('mailbox');
        if ( !empty($configs['enable_attachments']) && !empty($component) )
        {
            $attachmentCmp = new BASE_CLASS_FileAttachment('mailbox', $uidValue);
            $attachmentCmp->setInputSelector('#newMessageWindowAttachmentsBtn');
            $component->addComponent('attachments', $attachmentCmp);
        }

        $submit = new Submit("send");
        $submit->setValue($language->text('mailbox', 'send_button'));

        $this->addElement($submit);

        if ( !PEEP::getRequest()->isAjax() )
        {
            $this->initStatic();
        }
    }
    
    protected function initStatic()
    {
        $language = PEEP::getLanguage();
        $language->addKeyForJs('mailbox', 'close_new_message_window_confirmation');

        $messageError = $language->text('mailbox', 'create_conversation_fail_message');
        $messageSuccess = $language->text('mailbox', 'create_conversation_message');

        $js = "
        var newMessageFormModel = new PEEPMailbox.NewMessageForm.Model();
        PEEP.Mailbox.newMessageFormController = new PEEPMailbox.NewMessageForm.Controller(newMessageFormModel);

        PEEP.bind('mailbox.application_started', function(){
            PEEPMailbox.NewMessageForm.restoreForm();
        });

        peepForms['mailbox-new-message-form'].bind( 'success',
        function( json )
        {
            var from = $('#mailbox-new-message-form');

            if ( json.result == 'permission_denied' )
            {
                if ( json.message != undefined )
                {
                    PEEP.error(json.message);
                }
                else
                {
                    PEEP.error(". json_encode($language->text('mailbox', 'write_permission_denied')).");
                }
            }
            else if ( json.result == true )
            {
                var attUid = $(peepForms['mailbox-new-message-form'].elements.uid.input).val();
                var newUid = PEEPMailbox.uniqueId('mailbox_new_message_');
                PEEP.trigger('base.file_attachment', { 'uid': attUid, 'newUid': newUid });

                PEEP.Mailbox.lastMessageTimestamp = json.lastMessageTimestamp;

                peepForms['mailbox-new-message-form'].resetForm();

                $(peepForms['mailbox-new-message-form'].elements.uid.input).val(newUid);

                PEEP.trigger('mailbox.close_new_message_form');

                PEEP.info(json.message || '{$messageSuccess}');
            }
            else
            {
                PEEP.error(json.error);
            }
        } ); ";

        PEEP::getDocument()->addOnloadScript( $js, 3006 );
    }

    /**
     * Create new conversation
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param int $userId
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();
        $userId = PEEP::getUser()->getId();
        
        $language = PEEP::getLanguage();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        // Check if user can send message
        
        $error = null;
        
        $actionName = 'send_message';
        $userSendMessageIntervalOk = $conversationService->checkUserSendMessageInterval($userId);
        
        if (!$userSendMessageIntervalOk)
        {
            $send_message_interval = (int)PEEP::getConfig()->getValue('mailbox', 'send_message_interval');
            $error = array('result'=>false, 'error'=>$language->text('mailbox', 'feedback_send_message_interval_exceed', array('send_message_interval'=>$send_message_interval)));
        } 
        else if ( !PEEP::getUser()->isAuthorized('mailbox', $actionName) )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('mailbox', $actionName);
            if ( $status['status'] != BOL_AuthorizationService::STATUS_AVAILABLE )
            {
                $error = array('result' => false, 'error'=> $language->text('mailbox', $actionName.'_permission_denied'));
            }
        }
        
        $result = $error;
        
        if ( $error === null )
        {
            // Send message
            
            $files = BOL_AttachmentService::getInstance()->getFilesByBundleName('mailbox', $values['uid']);
            $result = $this->sendMessage($userId, $values["opponentId"], $values["subject"], $values["message"], $files);
        }
        
        PEEP::getSession()->delete('mailbox.new_message_form_attachments_uid');

        return $result;
    }
    
    protected function sendMessage( $userId, $opponentId, $subject, $message, $files = array() )
    {
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $checkResult = $conversationService->checkUser($userId, $opponentId);

        if ( $checkResult['isSuspended'] )
        {
            return array('result'=>false, 'error'=>$checkResult['suspendReasonMessage']);
        }

//        $message = UTIL_HtmlTag::stripTags(UTIL_HtmlTag::stripJs($message));
        $message = UTIL_HtmlTag::stripJs($message);
//        $message = nl2br($message);

        $event = new PEEP_Event('mailbox.before_create_conversation', array(
            'senderId' => $userId,
            'recipientId' => $opponentId,
            'message' => $message,
            'subject' => $subject
        ), array('result' => true, 'error' => '', 'message' => $message,  'subject' => $subject ));
        PEEP::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( empty($data['result']) )
        {
            return array('result'=> 'permission_denied', 'message' => $data['error']);
        }

        $subject = $data['subject'];
        $message = $data['message'];

        $conversation = $conversationService->createConversation($userId, $opponentId, htmlspecialchars($subject), $message);
        $messageDto = $conversationService->getLastMessage($conversation->id);

        if (!empty($files))
        {
            $conversationService->addMessageAttachments($messageDto->id, $files);
        }

        BOL_AuthorizationService::getInstance()->trackAction('mailbox', 'send_message');

        $conversationService->resetUserLastData($userId);
        $conversationService->resetUserLastData($opponentId);

        return array('result' => true, 'lastMessageTimestamp'=>$messageDto->timeStamp);
    }
}