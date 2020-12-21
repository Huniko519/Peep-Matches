<?php

class COREVISITOR_CMP_Login extends PEEP_Component
{
   const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';
   public function __construct( $ajax = false )
    {
        parent::__construct();
$sAddress = $_SERVER['HTTP_HOST'];
        $form = new COREVISITOR_CLASS_LoginForm("loginForm");
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $form->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_User', 'ajaxSignIn'));
        $this->addForm($form);
$form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if( data.result ){PEEP.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{PEEP.error(data.message);}}');
            $this->assign('forgot_url', PEEP::getRouter()->urlForRoute('base_forgot_password'));
$form->setAjaxResetOnSuccess(true);
            $form->setAjax();
       
        
        $this->assign('joinUrl', PEEP::getRouter()->urlForRoute('base_join'));
    }
}