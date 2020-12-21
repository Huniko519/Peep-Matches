<?php

class CONTACTUS_CTRL_Contact extends PEEP_ActionController
{

    public function index()
    {
        $this->setPageTitle(PEEP::getLanguage()->text('contactus', 'index_page_title'));
        $this->setPageHeading(PEEP::getLanguage()->text('contactus', 'index_page_heading'));

        $contactEmails = array();
        $contacts = CONTACTUS_BOL_Service::getInstance()->getDepartmentList();
        foreach ( $contacts as $contact )
        {
            /* @var $contact CONTACTUS_BOL_Department */
            $contactEmails[$contact->id]['label'] = CONTACTUS_BOL_Service::getInstance()->getDepartmentLabel($contact->id);
            $contactEmails[$contact->id]['email'] = $contact->email;
        }

        $form = new Form('contact_form');

        $fieldTo = new Selectbox('to');
        foreach ( $contactEmails as $id => $value )
        {
            $fieldTo->addOption($id, $value['label']);
        }
        $fieldTo->setRequired();
        $fieldTo->setHasInvitation(false);
        $fieldTo->setLabel($this->text('contactus', 'form_label_to'));
        $form->addElement($fieldTo);

        $fieldFrom = new TextField('from');
        $fieldFrom->setLabel($this->text('contactus', 'form_label_from'));
        $fieldFrom->setRequired();
        $fieldFrom->addValidator(new EmailValidator());
        
        if ( peep::getUser()->isAuthenticated() )
        {
            $fieldFrom->setValue( PEEP::getUser()->getEmail() );
        }
        
        $form->addElement($fieldFrom);

        $fieldSubject = new TextField('subject');
        $fieldSubject->setLabel($this->text('contactus', 'form_label_subject'));
        $fieldSubject->setRequired();
        $form->addElement($fieldSubject);

        $fieldMessage = new Textarea('message');
        $fieldMessage->setLabel($this->text('contactus', 'form_label_message'));
        $fieldMessage->setRequired();
        $form->addElement($fieldMessage);

        $fieldCaptcha = new CaptchaField('captcha');
        $fieldCaptcha->setLabel($this->text('contactus', 'form_label_captcha'));
        $form->addElement($fieldCaptcha);

        $submit = new Submit('send');
        $submit->setValue($this->text('contactus', 'form_label_submit'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                if ( !array_key_exists($data['to'], $contactEmails) )
                {
                    PEEP::getFeedback()->error($this->text('contactus', 'no_department'));
                    return;
                }

                $mail = PEEP::getMailer()->createMail();
                $mail->addRecipientEmail($contactEmails[$data['to']]['email']);
                $mail->setSender($data['from']);
                $mail->setSenderSuffix(false);
                $mail->setSubject($data['subject']);
                $mail->setTextContent($data['message']);
                PEEP::getMailer()->addToQueue($mail);

                PEEP::getSession()->set('contactus.dept', $contactEmails[$data['to']]['label']);
                $this->redirectToAction('sent');
            }
        }
    }

    public function sent()
    {
        $dept = null;

        if ( PEEP::getSession()->isKeySet('contactus.dept') )
        {
            $dept = PEEP::getSession()->get('contactus.dept');
            PEEP::getSession()->delete('contactus.dept');
        }
        else
        {
            $this->redirectToAction('index');
        }

        $feedback = $this->text('contactus', 'message_sent', ( $dept === null ) ? null : array('dept' => $dept));
        $this->assign('feedback', $feedback);
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return PEEP::getLanguage()->text($prefix, $key, $vars);
    }
}