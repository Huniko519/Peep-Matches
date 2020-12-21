<?php

class COREVISITOR_CLASS_LoginForm extends Form {
    public function __construct( $name ) {
        parent::__construct($name);

        $language = PEEP::getLanguage();
$username = new TextField('identity');
       $username->setRequired(true);
        $username->setHasInvitation(true);
        $username->setInvitation(PEEP::getLanguage()->text('base', 'component_sign_in_login_invitation'));
        $this->addElement($username);

       $password = new PasswordField('password');
       $password->setHasInvitation(true);
       $password->setInvitation('password');
       $password->setRequired(true);

      $this->addElement($password);

       $remeberMe = new CheckboxField('remember');
        $remeberMe->setLabel(PEEP::getLanguage()->text('base', 'sign_in_remember_me_label'));
        $this->addElement($remeberMe);

        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('base', 'sign_in_submit_label'));
        $this->addElement($submit);

    }
}