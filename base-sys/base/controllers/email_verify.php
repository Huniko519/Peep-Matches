<?php

class BASE_CTRL_EmailVerify extends PEEP_ActionController
{
    protected $questionService;
    protected $emailVerifyService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();

        $this->userService = BOL_UserService::getInstance();
    }

    protected function setMasterPage()
    {
         PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
    }

    public function index( $params )
    {
        if( PEEP::getRequest()->isAjax() )
        {
            echo "{message:'user is not verified'}";
            exit;
        }

        $this->setMasterPage();

        $userId = PEEP::getUser()->getId();

        if ( !PEEP::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( (int) $user->emailVerify === 1 )
        {
            $this->redirect(PEEP::getRouter()->uriForRoute('base_member_dashboard'));
        }
 $avatarService = BOL_AvatarService::getInstance();
        $userId = PEEP::getUser()->getId();
$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);
        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $emailVerifyForm = new Form('emailVerifyForm');

        $email = new TextField('email');
        $email->setLabel($language->text('base', 'questions_question_email_label'));
        //$email->setRequired();
        $email->addValidator(new EmailVerifyValidator());
        $email->setValue($user->email);

        $emailVerifyForm->addElement($email);

        $submit = new Submit('sendVerifyMail');
        $submit->setValue($language->text('base', 'email_verify_send_verify_mail_button_label'));

        $emailVerifyForm->addElement($submit);
        $this->addForm($emailVerifyForm);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $emailVerifyForm->isValid($_POST) )
            {
                $data = $emailVerifyForm->getValues();

                $email = htmlspecialchars(trim($data['email']));

                if ( $user->email != $email )
                {
                    BOL_UserService::getInstance()->updateEmail($user->id, $email);
                    $user->email = $email;
                }

                $this->emailVerifyService->sendUserVerificationMail($user);

                $this->redirect();
            }
        }
    }

    public function verify( $params )
    {
        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $code = null;
        if ( isset($params['code']) )
        {
            $code = $params['code'];
            $this->emailVerifyService->verifyEmail($code);
        }
    }

    public function verifyForm( $params )
    {
        $this->setMasterPage();
        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $form = new Form('verificationForm');
$avatarService = BOL_AvatarService::getInstance();
        $userId = PEEP::getUser()->getId();
$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);
        $verificationCode = new TextField('verificationCode');
        $verificationCode->setLabel($language->text('base', 'email_verify_verification_code_label'));
        $verificationCode->addValidator(new VerificationCodeValidator());

        $form->addElement($verificationCode);

        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'email_verify_verification_code_submit_button_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $code = $data['verificationCode'];

                $this->emailVerifyService->verifyEmail($code);
            }
        }
    }
}

class EmailVerifyValidator extends PEEP_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = PEEP::getLanguage();

        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $userId = PEEP::getUser()->getId();
            $user = BOL_UserService::getInstance()->findUserById($userId);

            if ( $user->email !== $value )
            {
                $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
                return false;
            }
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value )
                {
                },
        	getErrorMessage : function(){
                    return " . json_encode($this->getError()) . ";
                 }
        }";
    }
}

class VerificationCodeValidator extends PEEP_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $language = PEEP::getLanguage();

        $this->setErrorMessage($language->text('base', 'email_verify_invalid_verification_code'));
    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $emailVerifyData = BOL_EmailVerifyService::getInstance()->findByHash($value);

        if ( $emailVerifyData == null )
        {
            return false;
        }

        if( $emailVerifyData->type === BOL_EmailVerifyService::TYPE_USER_EMAIL )
        {
            $user = BOL_UserService::getInstance()->findUserById($emailVerifyData->userId);

            if ( $user == null )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                },
                getErrorMessage : function(){
                    return " . json_encode($this->getError()) . ";
                 }
        }";
    }
}
?>
