<?php

class CONTACTIMPORTER_CTRL_Email extends PEEP_ActionController
{
    public function send()
    {
        if( empty($_POST['emailList']) )
        {
            exit(json_encode(array( 'success' => false, 'message' => PEEP::getLanguage()->text('contactimporter', 'email_send_error_empty_email_list'))));
        }
        
        if( count($_POST['emailList']) > (int)PEEP::getConfig()->getValue('base', 'user_invites_limit'))
        {
            exit(json_encode(array( 'success' => false, 'message' => PEEP::getLanguage()->text('contactimporter', 'email_send_error_max_limit_message', array('limit' => (int)PEEP::getConfig()->getValue('base', 'user_invites_limit'))))));
        }

        $userId = PEEP::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        $vars = array(
            'inviter' => $displayName,
            'siteName' => PEEP::getConfig()->getValue('base', 'site_name'),
            'customMessage' => empty($_POST['text']) ? null : trim($_POST['text'])
        );

        foreach ( $_POST['emailList'] as $email )
        {
            $code = UTIL_String::getRandomString(20);
            BOL_UserService::getInstance()->saveUserInvitation($userId, $code);
            $vars['siteInviteURL'] = PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), array('code' => $code));

            $mail = PEEP::getMailer()->createMail();
            $mail->setSubject(PEEP::getLanguage()->text('contactimporter', 'mail_email_invite_subject', $vars));
            $mail->setHtmlContent(PEEP::getLanguage()->text('contactimporter', 'mail_email_invite_'. ( empty($_POST['text']) ? '' : 'msg_' ) .'html', $vars));
            $mail->setTextContent(PEEP::getLanguage()->text('contactimporter', 'mail_email_invite_'. ( empty($_POST['text']) ? '' : 'msg_' ) .'txt', $vars));
            $mail->addRecipientEmail($email);
            PEEP::getMailer()->addToQueue($mail);
        }

        exit(json_encode(array( 'success' =>true, 'message' => PEEP::getLanguage()->text('contactimporter', 'email_send_success', array( 'count' => count($_POST['emailList']) )))));
    }
}
