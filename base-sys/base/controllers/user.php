<?php

class BASE_CTRL_User extends PEEP_ActionController
{
    /**
     * @var BOL_UserService
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = BOL_UserService::getInstance();
    }

    public function forgotPassword()
    {
        if ( PEEP::getUser()->isAuthenticated() )
        {
            $this->redirect(PEEP_URL_HOME);
        }

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'forgot_password_heading'));

        $language = PEEP::getLanguage();

        $form = $this->userService->getResetForm();

        $this->addForm($form);

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                try
                {
                    $this->userService->processResetForm($data);
                }
                catch ( LogicException $e )
                {
                    PEEP::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                PEEP::getFeedback()->info($language->text('base', 'forgot_password_success_message'));
                $this->redirect();
            }
            else
            {
                PEEP::getFeedback()->error($language->text('base', 'forgot_password_general_error_message'));
                $this->redirect();
            }
        }
    }

    public function resetPasswordRequest()
    {
        if ( PEEP::getUser()->isAuthenticated() )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $form = $this->userService->getResetPasswordRequestFrom();
        $this->addForm($form);

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'reset_password_request_heading'));

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $resetPassword = $this->userService->findResetPasswordByCode($data['code']);

                if ( $resetPassword === null )
                {
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                    $this->redirect();
                }

                $this->redirect(PEEP::getRouter()->urlForRoute('base.reset_user_password', array('code' => $resetPassword->getCode())));
            }
            else
            {
                PEEP::getFeedback()->error(PEEP::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                $this->redirect();
            }
        }
    }

    public function resetPassword( $params )
    {
        $language = PEEP::getLanguage();

        if ( PEEP::getUser()->isAuthenticated() )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $this->setPageHeading($language->text('base', 'reset_password_heading'));

        if ( empty($params['code']) )
        {
            throw new Redirect404Exception();
        }

        $resetCode = $this->userService->findResetPasswordByCode($params['code']);

        if ( $resetCode == null )
        {
            throw new RedirectException(PEEP::getRouter()->urlForRoute('base.reset_user_password_expired_code'));
        }

        $user = $this->userService->findUserById($resetCode->getUserId());

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        $form = $this->userService->getResetPasswordForm();
        $this->addForm($form);

        $this->assign('formText', $language->text('base', 'reset_password_form_text', array('username' => $user->getUsername())));

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                try
                {
                    $this->userService->processResetPasswordForm($data, $user, $resetCode);
                }
                catch ( LogicException $e )
                {
                    PEEP::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'reset_password_success_message'));
                $this->redirect(PEEP::getRouter()->urlForRoute('static_sign_in'));
            }
            else
            {
                PEEP::getFeedback()->error('Invalid Data');
                $this->redirect();
            }
        }
    }

    public function resetPasswordCodeExpired()
    {
        $this->setPageHeading(PEEP::getLanguage()->text('base', 'reset_password_code_expired_cap_label'));
        $this->setPageHeadingIconClass('peep_ic_info');
        $this->assign('text', PEEP::getLanguage()->text('base', 'reset_password_code_expired_text', array('url' => PEEP::getRouter()->urlForRoute('base_forgot_password'))));
        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
    }

    public function standardSignIn()
    {
        if ( PEEP::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( PEEP::getUser()->isAuthenticated() )
        {
            throw new RedirectException(PEEP::getRouter()->getBaseUrl());
        }

        $this->assign('joinUrl', PEEP::getRouter()->urlForRoute('base_join'));

        PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));

        $this->addComponent('sign_in_form', new BASE_CMP_SignIn());

        if ( PEEP::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                PEEP::getFeedback()->error('Invalid data submitted!');
                $this->redirect();
            }

            $message = implode('', $result->getMessages());

            if ( $result->isValid() )
            {
                PEEP::getFeedback()->info($message);

                if ( empty($_GET['back-uri']) )
                {
                    $this->redirect();
                }

                $this->redirect(PEEP::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']));
            }
            else
            {
                PEEP::getFeedback()->error($message);
                $this->redirect();
            }
        }

        $this->setDocumentKey('base_sign_in');
    }

    public function ajaxSignIn()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( PEEP::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                exit(json_encode(array('result' => false, 'message' => 'Error!')));
            }

            $message = '';

            foreach ( $result->getMessages() as $value )
            {
                $message .= $value;
            }

            if ( $result->isValid() )
            {
                exit(json_encode(array('result' => true, 'message' => $message)));
            }
            else
            {
                exit(json_encode(array('result' => false, 'message' => $message)));
            }

            exit(json_encode(array()));
        }

        exit(json_encode(array()));
    }

    public function signOut()
    {

        PEEP::getUser()->logout();

        if ( isset($_COOKIE['peep_login']) )
        {
            setcookie('peep_login', '', time() - 3600, '/');
        }
        PEEP::getSession()->set('no_autologin', true);
        $this->redirect(PEEP::getRouter()->getBaseUrl());
    }
//    public static function getSignInForm( $submitDecorator = 'button' )
//    {
//        $form = new Form('sign-in');
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
//        $submit = new Submit('submit', $submitDecorator);
//        $submit->setValue(PEEP::getLanguage()->text('base', 'sign_in_submit_label'));
//        $form->addElement($submit);
//
//        return $form;
//    }

    /**
     * @return PEEP_AuthResult
     */
    private function processSignIn()
    {
        $form = $this->userService->getSignInForm();

        if ( !$form->isValid($_POST) )
        {
            throw new LogicException();
        }

        $data = $form->getValues();
        return $this->userService->processSignIn($data['identity'], $data['password'], isset($data['remember']));
    }

    public function controlFeatured( $params )
    {
        $service = BOL_UserService::getInstance();

        if ( (!PEEP::getUser()->isAuthenticated() || !PEEP::getUser()->isAuthorized('base') ) || ($userId = intval($params['id'])) <= 0 )
        {
            exit;
        }

        switch ( $params['command'] )
        {
            case 'mark':

                $service->markAsFeatured($userId);
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_marked_as_featured'));

                break;

            case 'unmark':

                $service->cancelFeatured($userId);
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_unmarked_as_featured'));

                break;
        }

        $this->redirect($_GET['backUrl']);
    }

    public function updateActivity( $params )
    {
        // activity already updated
        exit;
    }

    public function deleteUser( $params )
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $userId = (int) $params['user-id'];

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( $user === null || !PEEP::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error'
            )));
        }

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser($userId, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
        {
            exit(json_encode(array(
                'message' => PEEP::getLanguage()->text('base', 'cannot_delete_admin_user_message'),
                'result' => 'error'
            )));
        }

//        $event = new PEEP_Event(PEEP_EventManager::ON_USER_UNREGISTER, array('userId' => $userId, 'deleteContent' => true));
//        PEEP::getEventManager()->trigger($event);

        BOL_UserService::getInstance()->deleteUser($userId);

        $successMessage = PEEP::getLanguage()->text('base', 'user_deleted_page_message');

        if ( !empty($_GET['showMessage']) )
        {
            PEEP::getFeedback()->info($successMessage);
        }

        exit(json_encode(array(
            'message' => $successMessage,
            'result' => 'success'
        )));
    }

    public function userDeleted()
    {//TODO do smth
        //PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
    }

    public function approve( $params )
    {
        if ( !PEEP::getUser()->isAuthorized('base') )
        {
            throw new Redirect404Exception();
        }

        $userId = $params['userId'];

        $userService = BOL_UserService::getInstance();

        if ( $user = $userService->findUserById($userId) )
        {
            if ( !$userService->isApproved($userId) )
            {
                $userService->approve($userId);
                $userService->sendApprovalNotification($userId);

                PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_approved'));
            }
        }

        if ( empty($_SERVER['HTTP_REFERER']) )
        {
            $username = $userService->getUserName($userId);
            $this->redirect(PEEP::getRouter()->urlForRoute('base_user_profile', array('username' => $username)));
        }
        else
        {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function updateUserRoles()
    {
        if ( !PEEP::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error',
                'message' => 'Not Authorized'
            )));
        }

        $user = BOL_UserService::getInstance()->findUserById((int) $_POST['userId']);

        if ( $user === null )
        {
            exit(json_encode(array('result' => 'error', 'mesaage' => 'Empty user')));
        }

        $roles = array();
        foreach ( $_POST['roles'] as $roleId => $onoff )
        {
            if ( !empty($onoff) )
            {
                $roles[] = $roleId;
            }
        }

        $aService = BOL_AuthorizationService::getInstance();
        $aService->deleteUserRolesByUserId($user->getId());

        foreach ( $roles as $roleId )
        {
            $aService->saveUserRole($user->getId(), $roleId);
        }

        exit(json_encode(array(
            'result' => 'success',
            'message' => PEEP::getLanguage()->text('base', 'authorization_feedback_roles_updated')
        )));
    }

    public function block( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $userId = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->block($userId);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_profile_blocked'));

        $this->redirect($_GET['backUrl']);
    }

    public function unblock( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $id = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->unblock($id);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'user_feedback_profile_unblocked'));

        $this->redirect($_GET['backUrl']);
    }
}
