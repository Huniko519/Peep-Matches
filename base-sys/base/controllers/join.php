<?php

class BASE_CTRL_Join extends PEEP_ActionController
{
    const JOIN_CONNECT_HOOK = 'join_connect_hook';
    protected $responderUrl;
    protected $joinForm;

    public function __construct()
    {
        parent::__construct();

        $this->responderUrl = PEEP::getRouter()->urlFor("BASE_CTRL_Join", "ajaxResponder");

        $this->userService = BOL_UserService::getInstance();

    }

    public function index( $params )
    {
        $session = PEEP::getSession();

        if ( PEEP::getUser()->isAuthenticated() )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $language = PEEP::getLanguage();
        $this->setPageHeading($language->text('base', 'join_index'));
        
        //TODO DELETE config who_can_join from join
        if ( (int) PEEP::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            $code = null;
            if ( isset($_GET['code']) )
            {
                $code = $_GET['code'];
            }
            else if ( isset($params['code']) )
            {
                $code = $params['code'];
            }

            //close join form
            try
            {
                $event = new PEEP_Event(PEEP_EventManager::ON_JOIN_FORM_RENDER, array('code' => $code));
                PEEP::getEventManager()->trigger($event);
                $this->assign('notValidInviteCode', true);
                return;
            }
            catch ( JoinRenderException $ex )
            {
                //ignore;
            }
        }

        $urlParams = $_GET;
        if ( is_array($params) && !empty($params) )
        {
            $urlParams = array_merge($_GET, $params);
        }
        
        $this->joinForm = PEEP::getClassInstance('JoinForm', $this);
        $this->joinForm->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_Join', 'joinFormSubmit', $urlParams));
        $step = $this->joinForm->getStep();

        $this->addForm($this->joinForm);

        $language->addKeyForJs('base', 'join_error_username_not_valid');
        $language->addKeyForJs('base', 'join_error_username_already_exist');
        $language->addKeyForJs('base', 'join_error_email_not_valid');
        $language->addKeyForJs('base', 'join_error_email_already_exist');
        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

        //include js
        $onLoadJs = " window.join = new PEEP_BaseFieldValidators( " .
            json_encode(array(
                'formName' => $this->joinForm->getName(),
                'responderUrl' => $this->responderUrl,
                'passwordMaxLength' => UTIL_Validator::PASSWORD_MAX_LENGTH,
                'passwordMinLength' => UTIL_Validator::PASSWORD_MIN_LENGTH)) . ",
                " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . " ); ";

        PEEP::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = PEEP::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        PEEP::getDocument()->addScript($jsDir . "base_field_validators.js");

        $joinConnectHook = PEEP::getRegistry()->getArray(self::JOIN_CONNECT_HOOK);

        if ( !empty($joinConnectHook) )
        {
            $content = array();

            foreach ( $joinConnectHook as $function )
            {
                $result = call_user_func($function);

                if ( trim($result) )
                {
                    $content[] = $result;
                }
            }

            $this->assign('joinConnectHook', $content);
        }

        $this->setDocumentKey('registration_page');
    }

    public function joinFormSubmit( $params )
    {
	$this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCtrlViewDir().'join_index.html');

	//TODO DELETE config who_can_join from join
        if ( (int) PEEP::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
        {
            $code = null;
            if ( isset($params['code']) )
            {
                $code = $params['code'];
            }

            //close join form
            try
            {
                $event = new PEEP_Event(PEEP_EventManager::ON_JOIN_FORM_RENDER, array('code' => $code));
                PEEP::getEventManager()->trigger($event);
                $this->assign('notValidInviteCode', true);
                return;
            }
            catch ( JoinRenderException $ex )
            {
                //ignore;
            }
        }
        
        $this->index($params);
        $this->postProcess( $params );
    }

    protected function postProcess( $params )
    {
        if ( PEEP::getRequest()->isPost() )
        {
            if ( !$this->joinForm->isBot() )
            {
                if ( $this->joinForm->isValid($this->joinForm->getPost()) )
                {
                    $session = PEEP::getSession();
                    $joinData = $session->get(JoinForm::SESSION_JOIN_DATA);

                    if ( !isset($joinData) || !is_array($joinData) )
                    {
                        $joinData = array();
                    }

                    $data = $this->joinForm->getRealValues();

                    /*unset($data['repeatPassword']);*/
                    $this->joinForm->clearSession();

                    foreach ( $this->joinForm->questions as $question )
                    {
                        switch ( $question['presentation'] )
                        {
                            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX:

                                if ( is_array($data[$question['name']]) )
                                {
                                    $data[$question['name']] = array_sum($data[$question['name']]);
                                }
                                else
                                {
                                    $data[$question['name']] = 0;
                                }

                                break;
                        }
                    }

                    $joinData = array_merge($joinData, $data);

                    if ( $this->joinForm->isLastStep() )
                    {
                        $session->set(JoinForm::SESSION_JOIN_DATA, $joinData);
                        $this->joinUser($joinData, $this->joinForm->getAccountType(), $params);

                        $this->redirect(PEEP::getRouter()->getBaseUrl());
                    }
                    else
                    {
                        $step = $this->joinForm->getStep();

                        $step++;

                        $session->set(JoinForm::SESSION_JOIN_DATA, $joinData);
                        $session->set(JoinForm::SESSION_JOIN_STEP, $step);

                        $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), $params));

                    }
                }
            }
            else
            {
                $this->joinForm->clearSession();
                $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), $params));
            }
        }
        else
        {
            $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlForRoute('base_join'), $params));
        }
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];
        
        switch ( $command )
        {
            case 'isExistUserName':

                $result = false;

                $username = $_POST["value"];
                $result = $this->userService->isExistUserName($username);

                echo json_encode(array('result' => !$result));

                break;

            case 'isExistEmail':

                $result = false;

                $email = $_POST["value"];

                $result = $this->userService->isExistEmail($email);

                echo json_encode(array('result' => !$result));

                break;

            default:
        }
        exit;
    }

    protected function joinUser( $joinData, $accountType, $params )
    {
        $event = new PEEP_Event(PEEP_EventManager::ON_BEFORE_USER_REGISTER, $joinData);
        PEEP::getEventManager()->trigger($event);

        $language = PEEP::getLanguage();
        // create new user
        $user = $this->userService->createUser($joinData['username'], $joinData['password'], $joinData['email'], $accountType);

        $password = $joinData['password'];

        unset($joinData['username']);
        unset($joinData['password']);
        unset($joinData['email']);
        unset($joinData['accountType']);

        // save user data
        if ( !empty($user->id) )
        {
            if ( BOL_QuestionService::getInstance()->saveQuestionsData($joinData, $user->id) )
            {
                PEEP::getSession()->delete(JoinForm::SESSION_JOIN_DATA);
                PEEP::getSession()->delete(JoinForm::SESSION_JOIN_STEP);

                // authenticate user
                PEEP::getUser()->login($user->id);

                // create Avatar
                BOL_AvatarService::getInstance()->createAvatar($user->id, false, false);

                $event = new PEEP_Event(PEEP_EventManager::ON_USER_REGISTER, array('userId' => $user->id, 'method' => 'native', 'params' => $params));
                PEEP::getEventManager()->trigger($event);

                PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'join_successful_join'));

                if ( PEEP::getConfig()->getValue('base', 'confirm_email') )
                {
                    BOL_EmailVerifyService::getInstance()->sendUserVerificationMail($user);
                }
            }
            else
            {
                PEEP::getFeedback()->error($language->text('base', 'join_join_error'));
            }
        }
        else
        {
            PEEP::getFeedback()->error($language->text('base', 'join_join_error'));
        }
    }
}

class JoinForm extends BASE_CLASS_UserQuestionForm
{
    const SESSION_JOIN_DATA = 'joinData';

    const SESSION_JOIN_STEP = 'joinStep';

    const SESSION_REAL_QUESTION_LIST = 'join.real_question_list';

    const SESSION_ALL_QUESTION_LIST = 'join.all_question_list';

    const SESSION_START_STAMP = 'join.session_start_stamp';

    protected $post = array();
    protected $stepCount = 1;
    protected $isLastStep = false;
    protected $displayAccountType = false;
    public  $questions = array();
    protected $sortedQuestionsList = array();
    protected $questionListBySection = array();
    protected $questionValuesList = array();
    protected $accountType = null;
    protected $isBot = false;
    protected $data = array();
    protected $toggleClass = '';

    public function __construct( $controller )
    {
        parent::__construct('joinForm');

        $this->setId('joinForm');

        $stamp = PEEP::getSession()->get(self::SESSION_START_STAMP);

        if ( empty($stamp) )
        {
            PEEP::getSession()->set(self::SESSION_START_STAMP, time());
        }

        unset($stamp);

        $this->checkSession();

        $stepCount = 1;
        $joinSubmitLabel = "";

        // get available account types from DB
        $accounts = $this->getAccountTypes();

        $joinData = PEEP::getSession()->get(self::SESSION_JOIN_DATA);

        if ( !isset($joinData) || !is_array($joinData) )
        {
            $joinData = array();
        }

        $accountsKeys = array_keys($accounts);
        $this->accountType = $accountsKeys[0];

        if ( isset($joinData['accountType']) )
        {
            $this->accountType = trim($joinData['accountType']);
        }

        $step = $this->getStep();

        if ( count($accounts) > 1 )
        {
            $this->stepCount = 2;
            switch ( $step )
            {
                case 1:
                    $this->displayAccountType = true;
                    $joinSubmitLabel = PEEP::getLanguage()->text('base', 'join_submit_button_continue');
                    break;

                case 2:
                    $this->isLastStep = true;
                    $joinSubmitLabel = PEEP::getLanguage()->text('base', 'join_submit_button_join');
                    break;
            }
        }
        else
        {
            $this->isLastStep = true;
            $joinSubmitLabel = PEEP::getLanguage()->text('base', 'join_submit_button_join');
        }

        $joinSubmit = new Submit('joinSubmit');
        $joinSubmit->addAttribute('class', 'peep_button peep_ic_submit');
        $joinSubmit->setValue($joinSubmitLabel);
        $this->addElement($joinSubmit);

        if ( $this->displayAccountType )
        {
            $joinAccountType = new Selectbox('accountType');
            $joinAccountType->setLabel(PEEP::getLanguage()->text('base', 'questions_question_account_type_label'));
            $joinAccountType->setRequired();
            $joinAccountType->setOptions($accounts);
            $joinAccountType->setValue($this->accountType);
            $joinAccountType->setHasInvitation(false);

            $this->addElement($joinAccountType);
        }

        $this->getQuestions();

        $section = null;
        //$this->questionListBySection = array();
        $questionNameList = array();
        $this->sortedQuestionsList = array();

        foreach ( $this->questions as $sort => $question )
        {
            if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
            {
                if ( $section !== $question['sectionName'] )
                {
                    $section = $question['sectionName'];
                }

                //$this->questionListBySection[$section][] = $this->questions[$sort];
                $questionNameList[] = $this->questions[$sort]['name'];
                $this->sortedQuestionsList[] = $this->questions[$sort];
            }
        }

        $this->questionValuesList = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addFakeQuestions();

        $this->addQuestions($this->sortedQuestionsList, $this->questionValuesList, $this->updateJoinData());

        $this->setQuestionsLabel();

        $this->addClassToBaseQuestions();

        if ( $this->isLastStep )
        {
            $this->addLastStepQuestions($controller);
        }

        $controller->assign('step', $step);
        $controller->assign('questionArray', $this->questionListBySection);
        $controller->assign('displayAccountType', $this->displayAccountType);
        $controller->assign('isLastStep', $this->isLastStep);
    }

    public function checkSession()
    {
        $stamp = BOL_QuestionService::getInstance()->getQuestionsEditStamp();
        $sessionStamp = PEEP::getSession()->get(self::SESSION_START_STAMP);
        
        if ( !empty($sessionStamp) && $stamp > $sessionStamp )
        {
            PEEP::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
            PEEP::getSession()->delete(self::SESSION_JOIN_DATA);
            PEEP::getSession()->delete(self::SESSION_JOIN_STEP);
            PEEP::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
            PEEP::getSession()->delete(self::SESSION_START_STAMP);

            if ( PEEP::getRequest()->isPost() )
            {
                UTIL_Url::redirect(PEEP::getRouter()->urlForRoute('base_join'));
            }
        }
    }

    public function setQuestionsLabel()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                $this->getElement($question['name'])->setLabel(PEEP::getLanguage()->text('base', 'questions_question_' . $question['realName'] . '_label'));
            }
        }
    }

    public function addClassToBaseQuestions()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                if ( $question['realName'] == 'username' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "peep_username_validator");
                }

                if ( $question['realName'] == 'email' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "peep_email_validator");
                }
            }
        }
    }

    protected function toggleQuestionClass()
    {
        $class = 'peep_alt1';
        switch ( $this->toggleClass )
        {
            case null:
            case 'peep_alt2':
                break;
            case 'peep_alt1':
                $class = 'peep_alt2';
        }

        $this->toggleClass = $class;

        return $class;
    }

    protected function randQuestionClass()
    {
        $rand = rand(0, 1);

        if ( !$rand )
        {
            $class = 'peep_alt1';
        }
        else
        {
            $class = 'peep_alt2';
        }

        return $class;
    }

    protected function addFakeQuestions()
    {
        $step = $this->getStep();
        $realQuestionList = array();
        $valueList = $this->questionValuesList;
        $this->questionValuesList = array();
        $this->sortedQuestionsList = array();
        $this->questionListBySection = array();
        $section = '';

        $oldQuestionList = PEEP::getSession()->get(self::SESSION_REAL_QUESTION_LIST);
        $allQuestionList = PEEP::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        if ( !empty($oldQuestionList) && !empty($oldQuestionList) )
        {
            $realQuestionList = $oldQuestionList;
            $this->sortedQuestionsList = $allQuestionList;

            foreach ( $this->sortedQuestionsList as $key => $question )
            {
                $this->questionListBySection[$question['sectionName']][] = $question;

                if ( $question['fake'] == true )
                {
                    $this->addDisplayNoneClass(preg_replace('/\s+(peep_alt1|peep_alt2)/', '', $question['trClass']));
                }
                else
                {
                    $this->addEmptyClass(preg_replace('/\s+(peep_alt1|peep_alt2)/', '', $question['trClass']));
                }
                
                if ( !empty($valueList[$question['realName']]) )
                {
                    $this->questionValuesList[$question['name']] = $valueList[$question['realName']];
                }
            }
        }
        else
        {
            foreach ( $this->questions as $sort => $question )
            {
                if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
                {
                    if ( $section !== $question['sectionName'] )
                    {
                        $section = $question['sectionName'];
                    }

                    $event = new PEEP_Event('base.questions_field_add_fake_questions', $question, true);

                    PEEP::getEventManager()->trigger($event);

                    $addFakes = $event->getData();

                    if ( !$addFakes || $this->questions[$sort]['presentation'] == 'password' )
                    {
                        $this->questions[$sort]['fake'] = false;
                        $this->questions[$sort]['realName'] = $question['name'];

                        $this->questions[$sort]['trClass'] = $this->toggleQuestionClass();

                        if ( $this->questions[$sort]['presentation'] == 'password' )
                        {
                            $this->toggleQuestionClass();
                        }

                        $this->sortedQuestionsList[$question['name']] = $this->questions[$sort];
                        $this->questionListBySection[$section][] = $this->questions[$sort];
                        
                        if ( !empty($valueList[$question['name']]) )
                        {
                            $this->questionValuesList[$question['name']] = $valueList[$question['name']];
                        }
                        
                        continue;
                    }

                    $fakesCount = rand(2, 5);
                    $fakesCount = $fakesCount + 1;
                    $randId = rand(0, $fakesCount);

                    for ( $i = 0; $i <= $fakesCount; $i++ )
                    {
                        $randName = uniqid(UTIL_String::getRandomString(rand(5, 13), 2)); 
                        $question['trClass'] = uniqid('peep_'. UTIL_String::getRandomString(rand(5, 10), 2));

                        if ( $i == $randId )
                        {
                            $realQuestionList[$randName] = $this->questions[$sort]['name'];
                            $question['fake'] = false;
                            $question['required'] = $this->questions[$sort]['required'];

                            $this->addEmptyClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->toggleQuestionClass();

                        }
                        else
                        {
                            $question['required'] = 0;
                            $question['fake'] = true;

                            $this->addDisplayNoneClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->randQuestionClass();
                        }
                        
                        $question['realName'] = $this->questions[$sort]['name'];

                        $question['name'] = $randName;

                        $this->sortedQuestionsList[$randName] = $question;

                        if ( !empty($valueList[$this->questions[$sort]['name']]) )
                        {
                            $this->questionValuesList[$randName] = $valueList[$this->questions[$sort]['name']];
                        }

                        $this->questionListBySection[$section][] = $question;
                    }
                }
            }
        }

        if ( PEEP::getRequest()->isPost() )
        {
            $this->post = $_POST;

            if ( empty($oldQuestionList) )
            {
                $oldQuestionList = array();
            }

            if ( empty($allQuestionList) )
            {
                $allQuestionList = array();
            }

            if ( $oldQuestionList && $allQuestionList )
            {
                foreach ( $oldQuestionList as $key => $value )
                {
                    $newKey = array_search($value, $realQuestionList);

                    if ( $newKey !== false && isset($_POST[$key]) && isset($realQuestionList[$newKey]) )
                    {
                        $this->post[$newKey] = $_POST[$key];
                    }
                }

                foreach ( $allQuestionList as $question )
                {
                    if ( !empty($question['fake']) && !empty($_POST[$question['name']]) )
                    {
                        $this->isBot = true;
                    }
                }
            }
        }

        if ( $this->isBot )
        {
            $event = new PEEP_Event('base.bot_detected', array('isBot' => true));
            PEEP::getEventManager()->trigger($event);
        }

        PEEP::getSession()->set(self::SESSION_REAL_QUESTION_LIST, $realQuestionList);
        PEEP::getSession()->set(self::SESSION_ALL_QUESTION_LIST, $this->sortedQuestionsList);
    }

    protected function updateJoinData()
    {
        $joinData = PEEP::getSession()->get(self::SESSION_JOIN_DATA);

        if ( empty($joinData) )
        {
            return;
        }

        $this->data = $joinData;

        $list = PEEP::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        if ( !empty($list) )
        {
            foreach ( $list as $fakeName => $realName )
            {
                if ( !empty($joinData[$realName]) )
                {
                    unset($this->data[$realName]);
                    $this->data[$fakeName] = $joinData[$realName];
                }
            }
        }

        return $this->data;
    }

    public function getRealValues()
    {
        $list = $this->sortedQuestionsList;

        $values = $this->getValues();
        $result = array();

        if ( !empty($list) )
        {
            foreach ( $values as $fakeName => $value )
            {
                if ( !empty($list[$fakeName]) && isset($list[$fakeName]['fake']) && $list[$fakeName]['fake'] == false )
                {
                    $result[$list[$fakeName]['realName']] = $value;
                }

                if ( $fakeName == 'accountType' )
                {
                    $result[$fakeName] = $value;
                }
            }
        }
        
        return $result;
    }

    public function getStep()
    {
        $session = PEEP::getSession();

        $step = $session->get(self::SESSION_JOIN_STEP);

        if ( isset($step) )
        {
            $step = (int) $step;

            if ( $step === 0 )
            {
                $step = 1;
                $session->set(self::SESSION_JOIN_STEP, $step);
            }
        }
        else
        {
            $step = 1;
            $session->set(self::SESSION_JOIN_STEP, $step);
        }

        return $step;
    }

    public function getQuestions()
    {
        $this->questions = array();

        if ( $this->isLastStep )
        {
            $this->questions = BOL_QuestionService::getInstance()->findSignUpQuestionsForAccountType($this->accountType);
        }
        else
        {
            $this->questions = BOL_QuestionService::getInstance()->findBaseSignUpQuestions();
        }
    }
    
    protected function addLastStepQuestions( $controller )
    {
        $displayPhoto = false;

        $displayPhotoUpload = PEEP::getConfig()->getValue('base', 'join_display_photo_upload');
        $avatarValidator = PEEP::getClassInstance("BASE_CLASS_AvatarFieldValidator", false);

        switch ( $displayPhotoUpload )
        {
            case BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD :
                $avatarValidator = PEEP::getClassInstance("BASE_CLASS_AvatarFieldValidator", true);

            case BOL_UserService::CONFIG_JOIN_DISPLAY_PHOTO_UPLOAD :
                $userPhoto = PEEP::getClassInstance("BASE_CLASS_JoinUploadPhotoField", 'userPhoto');
                $userPhoto->setLabel(PEEP::getLanguage()->text('base', 'questions_question_user_photo_label'));
                $userPhoto->addValidator($avatarValidator);
                $this->addElement($userPhoto);

                $displayPhoto = true;
        }

        $displayTermsOfUse = false;

        if ( PEEP::getConfig()->getValue('base', 'join_display_terms_of_use') )
        {
            $termOfUse = new CheckboxField('termOfUse');
            $termOfUse->setLabel(PEEP::getLanguage()->text('base', 'questions_question_user_terms_of_use_label'));
            $termOfUse->setRequired();

            $this->addElement($termOfUse);

            $displayTermsOfUse = true;
        }

        $this->setEnctype('multipart/form-data');

        $event = new PEEP_Event('join.get_captcha_field');
        PEEP::getEventManager()->trigger($event);
        $captchaField = $event->getData();

        $displayCaptcha = false;

        if ( !empty($captchaField) && $captchaField instanceof FormElement )
        {
            $captchaField->setName('captchaField');
            $this->addElement($captchaField);
            $displayCaptcha = true;
        }
        //$captchaField = new CaptchaField('captchaField');

        //$this->addElement($captchaField);

        $controller->assign('display_captcha', $displayCaptcha);
        $controller->assign('display_photo', $displayPhoto);
        $controller->assign('display_terms_of_use', $displayTermsOfUse);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( !empty($captchaField) && $captchaField instanceof FormElement )
            {
                $captchaField->setValue(null);
            }

            if ( isset($userPhoto) && isset($_FILES[$userPhoto->getName()]['name']) )
            {
                $_POST[$userPhoto->getName()] = $_FILES[$userPhoto->getName()]['name'];
            }
        }
    }

    protected function addFieldValidator( $formField, $question )
    {
        $list = PEEP::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        $questionInfo = empty($list[$question['name']]) ? null : $list[$question['name']];

        if ( (string) $question['base'] === '1' )
        {
            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'email' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new joinEmailValidator());
            }

            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'username' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new UserNameValidator());
            }

           /* if ( $question['name'] === 'password' )
            {
                $passwordRepeat = BOL_QuestionService::getInstance()->getPresentationClass($question['presentation'], 'repeatPassword');
                $passwordRepeat->setLabel(PEEP::getLanguage()->text('base', 'questions_question_repeat_password_label'));
                $passwordRepeat->setRequired((string) $question['required'] === '1');
                $this->addElement($passwordRepeat);

                $formField->addValidator(new PasswordValidator());
            }*/
        }
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        $valuesArray = array();

        $realQuestionList = PEEP::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        $name = $questionName;
        if ( !empty($realQuestionList[$questionName]) )
        {
            $name = $realQuestionList[$questionName];
        }

        parent::setFieldOptions($formField, $name, $questionValues);

        /* foreach ( $questionValues as $values )
        {
            if ( is_array($values) )
            {
                foreach ( $values as $value )
                {
                    $valuesArray[($value->value)] = PEEP::getLanguage()->text('base', 'questions_question_' . $name . '_value_' . ($value->value));
                }
            }
        }

        $formField->setOptions($valuesArray); */
    }

    public function isBot()
    {
        return $this->isBot;
    }

    public function isLastStep()
    {
        return $this->isLastStep;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function addEmptyClass( $className )
    {
        PEEP::getDocument()->addStyleDeclaration("
            .{$className}
            {
                
            } ");
    }

    public function addDisplayNoneClass( $className )
    {
        PEEP::getDocument()->addStyleDeclaration("
            .{$className}
            {
                display:none;
            } ");
    }

    public function clearSession()
    {
        PEEP::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
        PEEP::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
    }

    public function getSortedQuestionsList()
    {
        return $this->sortedQuestionsList;
    }
}

class UserNameValidator extends PEEP_Validator
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
        if ( !UTIL_Validator::isUserNameValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_not_valid'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistUserName($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_already_exist'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isRestrictedUsername($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_username_restricted'));
            return false;
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
                    // window.join.validateUsername(false);
                    if( window.join.errors['username']['error'] !== undefined )
                    {
                        throw window.join.errors['username']['error'];
                    }
                },
                getErrorMessage : function(){
                    if( window.join.errors['username']['error'] !== undefined ){ return window.join.errors['username']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class joinEmailValidator extends PEEP_Validator
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
            $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
            return false;
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
                    // window.join.validateEmail(false);
                    if( window.join.errors['email']['error'] !== undefined )
                    {
                        throw window.join.errors['email']['error'];
                    }
                },
        	getErrorMessage : function(){
                    if( window.join.errors['email']['error'] !== undefined ){ return window.join.errors['email']['error']; }
                    else{ return " . json_encode($this->getError()) . " }
                 }
        }";
    }
}

class PasswordValidator extends BASE_CLASS_PasswordValidator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        parent::__construct();
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
                    if( !window.join.validatePassword() )
                    {
                        throw window.join.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.join.errors['password']['error'] !== undefined ){ return window.join.errors['password']['error'] }
                       else{ return " . json_encode($this->getError()) . " }
                }
        }";
    }
}

class JoinRenderException extends Exception
{
    
}