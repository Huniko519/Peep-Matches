<?php

class BASE_CTRL_UserSearch extends PEEP_ActionController
{

    public function __construct()
    {
        parent::__construct();

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(PEEP::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageTitle(PEEP::getLanguage()->text('base', 'user_search_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_user');
    }

    public function index()
    {
        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        if ( !PEEP::getUser()->isAuthorized('base', 'search_users') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            $this->assign('authMessage', $status['msg']);
            return;
        }

        $mainSearchForm = PEEP::getClassInstance('MainSearchForm', $this);
        $mainSearchForm->process($_POST);
        $this->addForm($mainSearchForm);

        $displayNameSearchForm = new DisplayNameSearchForm($this);
        $displayNameSearchForm->process($_POST);
        $this->addForm($displayNameSearchForm);
    }

    public function result()
    {
        if ( !PEEP::getUser()->isAuthorized('base', 'search_users') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
            throw new AuthorizationException($status['msg']);
        }

        PEEP::getDocument()->setDescription(PEEP::getLanguage()->text('base', 'users_list_user_search_meta_description'));

        $this->addComponent('menu', BASE_CTRL_UserList::getMenu('search'));

        $language = PEEP::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = PEEP::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;

        $count = $rpp;

        $listId = PEEP::getSession()->get(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE);
        $list = BOL_UserService::getInstance()->findSearchResultList($listId, $first, $count);
        $itemCount = BOL_SearchService::getInstance()->countSearchResultItem($listId);

        $cmp = new BASE_CLASS_SearchResultList($list, $itemCount, $rpp, true);

        $this->addComponent('cmp', $cmp);
        $this->assign('listType', 'search');

        $searchUrl = PEEP::getRouter()->urlForRoute('users-search');
        $this->assign('searchUrl', $searchUrl);
    }
}

class MainSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'MainSearchFormSubmit';

    const FORM_SESSEION_VAR = 'MAIN_SEARCH_FORM_DATA';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var PEEP_ActionController $controller
     * 
     */

    public function __construct( $controller )
    {
        parent::__construct('MainSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = PEEP::getLanguage();

        $this->setId('MainSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(PEEP::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionData = PEEP::getSession()->get(self::FORM_SESSEION_VAR);

        if ( $questionData === null )
        {
            $questionData = array();
        }

        $accounts = $this->getAccountTypes();

        $accountList = array();
        $accountList[BOL_QuestionService::ALL_ACCOUNT_TYPES] = PEEP::getLanguage()->text('base', 'questions_account_type_' . BOL_QuestionService::ALL_ACCOUNT_TYPES);

        foreach ( $accounts as $key => $account )
        {
            $accountList[$key] = $account;
        }

        $keys = array_keys($accountList);

        $this->accountType = $keys[0];

        if ( isset($questionData['accountType']) && in_array($questionData['accountType'], $keys) )
        {
            $this->accountType = $questionData['accountType'];
        }

        if ( count($accounts) > 1 )
        {
            $this->displayAccountType = true;

            $accountType = new Selectbox('accountType');
            $accountType->setLabel(PEEP::getLanguage()->text('base', 'questions_question_account_type_label'));
            $accountType->setRequired();
            $accountType->setOptions($accountList);
            $accountType->setValue($this->accountType);
            $accountType->setHasInvitation(false);

            $this->addElement($accountType);
        }

        $questions = $questionService->findSearchQuestionsForAccountType($this->accountType);

        $mainSearchQuestion = array();
        $questionNameList = array();

        foreach ( $questions as $key => $question )
        {
            $sectionName = $question['sectionName'];
            $mainSearchQuestion[$sectionName][] = $question;
            $questionNameList[] = $question['name'];
            $questions[$key]['required'] = '0';
        }

        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $controller->assign('questionList', $mainSearchQuestion);
        $controller->assign('displayAccountType', $this->displayAccountType);
    }

    public function process( $data )
    {
        if ( PEEP::getRequest()->isPost() && !$this->isAjax() && isset($data['form_name']) && $data['form_name'] === $this->getName() )
        {
            PEEP::getSession()->set(self::FORM_SESSEION_VAR, $data);

            if ( isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
            {
                if ( !PEEP::getUser()->isAuthorized('base', 'search_users') )
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');;
                    PEEP::getFeedback()->warning($status['msg']);
                    $this->controller->redirect();
                }
                
                if ( isset($data['accountType']) && $data['accountType'] === BOL_QuestionService::ALL_ACCOUNT_TYPES )
                {
                    unset($data['accountType']);
                }
                
                $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
                $listId = 0;

                if ( count($userIdList) > 0 )
                {
                    $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
                }

                PEEP::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

                BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

                $this->controller->redirect(PEEP::getRouter()->urlForRoute("users-search-result", array()));
            }
            $this->controller->redirect(PEEP::getRouter()->urlForRoute("users-search"));
        }
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }

    protected function setFieldValue( $formField, $presentation, $value )
    {

    }
}

class DisplayNameSearchForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'DisplayNameSearchFormSubmit';

    public $controller;
    public $accountType;
    public $displayAccountType = false;
    public $displayMainSearch = true;

    /*
     * @var PEEP_ActionController $controller
     *
     */

    public function __construct( $controller )
    {
        parent::__construct('DisplayNameSearchForm');

        $this->controller = $controller;

        $questionService = BOL_QuestionService::getInstance();
        $language = PEEP::getLanguage();

        $this->setId('DisplayNameSearchForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(PEEP::getLanguage()->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');

        $question = $questionService->findQuestionByName($questionName);

        $questionPropertyList = array();
        foreach ( $question as $property => $value )
        {
            $questionPropertyList[$property] = $value;
        }

        $this->addQuestions(array($questionName => $questionPropertyList), array(), array());

        $controller->assign('displayNameQuestion', $questionPropertyList);
    }

    public function process( $data )
    {
        if ( PEEP::getRequest()->isPost() && isset($data[self::SUBMIT_NAME]) && $this->isValid($data) && !$this->isAjax() )
        {
            if ( !PEEP::getUser()->isAuthorized('base', 'search_users') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'search_users');
                PEEP::getFeedback()->warning($status['msg']);
                $this->controller->redirect();
            }
            
            $userIdList = BOL_UserService::getInstance()->findUserIdListByQuestionValues($data, 0, BOL_SearchService::USER_LIST_SIZE);
            $listId = 0;

            if ( count($userIdList) > 0 )
            {
                $listId = BOL_SearchService::getInstance()->saveSearchResult($userIdList);
            }

            PEEP::getSession()->set(BOL_SearchService::SEARCH_RESULT_ID_VARIABLE, $listId);

            BOL_AuthorizationService::getInstance()->trackAction('base', 'search_users');

            $this->controller->redirect(PEEP::getRouter()->urlForRoute("users-search-result", array()));
        }
    }
}
