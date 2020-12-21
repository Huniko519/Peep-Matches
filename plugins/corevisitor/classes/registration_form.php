<?php

class COREVISITOR_CLASS_RegistrationForm extends Form {
    public function __construct( $name ) {
        parent::__construct($name);

        $language = PEEP::getLanguage();


        $oEmail = new TextField("email");
        $oEmail->addAttribute('placeholder', PEEP::getLanguage()->text('base', 'questions_question_email_label'));
        $oEmail->addValidator(new EmailValidator());
        $oEmail->setRequired();
        $this->addElement($oEmail);

       $joinSubmitLabel = PEEP::getLanguage()->text('base', 'join_submit_button_continue');

        $oSignUp = new Submit("joinSubmit");
       
$oSignUp->setValue($joinSubmitLabel);
        $this->addElement($oSignUp);
    }
}