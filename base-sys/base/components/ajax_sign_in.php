<?php

//class BASE_CMP_AjaxSignIn extends PEEP_Component
//{
//    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';
//
//    /**
//     * Constructor.
//     */
//    public function __construct( $formName, $ajax = false )
//    {
//        parent::__construct();
//
//        $form = new Form($formName);
//
//        $form->setAjaxResetOnSuccess(false);
//
//        $username = new TextField('identity');
//        $username->setRequired(true);
//        $username->setHasInvitation(true);
//        $username->setInvitation(PEEP::getLanguage()->text('base', 'component_sign_in_login_invitation'));
//        $form->addElement($username);
//
//        $password = new PasswordField('password');
//        $password->setHasInvitation(true);
//        $password->setInvitation('password');
//        $password->setRequired(true);
//
//        $form->addElement($password);
//
//        $remeberMe = new CheckboxField('remember');
//        $remeberMe->setLabel(PEEP::getLanguage()->text('base', 'sign_in_remember_me_label'));
//        $form->addElement($remeberMe);
//
//        $submit = new Submit('submit');
//        $submit->setValue(PEEP::getLanguage()->text('base', 'sign_in_submit_label'));
//        $form->addElement($submit);
//
//
//
//
//        $form = BASE_CTRL_User::getSignInForm();
//        $form->setAjax();
//        $form->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_User', 'ajaxSignIn'));
//        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if( data.result ){PEEP.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{PEEP.error(data.message);}}');
//        $this->addForm($form);
//        $this->assign('forgot_url', PEEP::getRouter()->urlForRoute('base_forgot_password'));
//        $this->assign('buttonList', implode('', $this->getRemoteAuthButtonList()));
//    }
//
//    private function getRemoteAuthButtonList()
//    {
//        $items = PEEP::getRegistry()->getArray(self::HOOK_REMOTE_AUTH_BUTTON_LIST);
//
//        if ( empty($items) )
//        {
//            return array();
//        }
//
//        $tplItems = array();
//        foreach ( $items as $item )
//        {
//            if ( is_callable($item) )
//            {
//                $tplItems[] = call_user_func($item);
//            }
//        }
//
//        return array_filter($tplItems);
//    }
//}