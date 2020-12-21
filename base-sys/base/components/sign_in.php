<?php

class BASE_CMP_SignIn extends PEEP_Component
{
    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';

    /**
     * Constructor.
     */
    public function __construct( $ajax = false )
    {
        parent::__construct();
        $form = BOL_UserService::getInstance()->getSignInForm('sign-in', true);

        $this->addForm($form);

        if ( $ajax )
        {
            $form->setAjaxResetOnSuccess(false);
            $form->setAjax();
            $form->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_User', 'ajaxSignIn'));
            $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if( data.result ){PEEP.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{PEEP.error(data.message);}}');
            $this->assign('forgot_url', PEEP::getRouter()->urlForRoute('base_forgot_password'));
        }

        $this->assign('joinUrl', PEEP::getRouter()->urlForRoute('base_join'));
    }
}