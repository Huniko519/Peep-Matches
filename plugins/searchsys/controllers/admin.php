<?php

class SEARCHSYS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    const SESSION_VAR_ACCOUNT_TYPE = "BASE_QUESTION_ACCOUNT_TYPE";
    
    /**
     * @var BOL_QuestionService
     */
    private $questionService;
    
    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
    }
    
    /**
     * Default action
     */
    public function index()
    {
        if ( SEARCHSYS_BOL_Service::getInstance()->isPeepsys() )
        {
            $this->redirect(PEEP::getRouter()->urlForRoute('searchsys.admin-site-search'));
        }
        
        $lang = PEEP::getLanguage();
        
        $accountType = null;
        if ( isset($_GET['accountType']) )
        {
            PEEP::getSession()->set(self::SESSION_VAR_ACCOUNT_TYPE, trim($_GET['accountType']));
        }

        if ( PEEP::getSession()->get(self::SESSION_VAR_ACCOUNT_TYPE) )
        {
            $accountType = PEEP::getSession()->get(self::SESSION_VAR_ACCOUNT_TYPE);
        }
        
        // get available account types from DB
        $accountTypes = $this->questionService->findAllAccountTypesWithQuestionsCount();

        /* @var $value BOL_QuestionAccountType */
        foreach ( $accountTypes as $key => $value )
        {
            $accounts[$value['name']] = $lang->text('base', 'questions_account_type_' . $value['name']);
        }

        $this->assign('displayAccountType', !empty($accounts) && count($accounts) > 1);
            
        $accountsKeys = array_keys($accounts);
        $accountType = (!isset($accountType) || !in_array($accountType, $accountsKeys) ) ? $accountsKeys[0] : $accountType;
        $this->assign('accountType', $accountType);
        
        $config = PEEP::getConfig();
        
        $questionsConf = json_decode($config->getValue('searchsys', 'questions'), true);
        if ( !empty($questionsConf[$accountType]) )
        {
            $this->assign('questionsConf', $questionsConf[$accountType]);
        }
        
        if ( PEEP::getRequest()->isPost() && !empty($_POST['action']) )
        {
	        switch ( $_POST['action'] )
	        {
	        	case 'update_questions':
	        	    $questionsConf[$accountType] = array();
	        		foreach ( $_POST['questions'] as $name => $question )
	        		{
	        			$questionsConf[$accountType][$name] = !empty($_POST['questions'][$name]) && $_POST['questions'][$name];
	        		}
	        		$config->saveConfig('searchsys', 'questions', json_encode($questionsConf));
	        		PEEP::getFeedback()->info($lang->text('searchsys', 'settings_updated'));
	        		$this->redirect();
	        		break;
	        		
	        	case 'update_settings':
	        		$config->saveConfig('searchsys', 'show_advanced', !empty($_POST['show_advanced']));
	        		$config->saveConfig('searchsys', 'show_section', !empty($_POST['show_section']));
	        		$config->saveConfig('searchsys', 'username_search', !empty($_POST['username_search']));
	        		$config->saveConfig('searchsys', 'online_only_enabled', !empty($_POST['online_only']));
	        		$config->saveConfig('searchsys', 'with_photo_enabled', !empty($_POST['with_photo']));
	        		
                    PEEP::getFeedback()->info($lang->text('searchsys', 'settings_updated'));
	        		$this->redirect();
	        		break;
	        }
        }
        
        $this->assign('showAdvanced', $config->getValue('searchsys', 'show_advanced'));
        $this->assign('showSection', $config->getValue('searchsys', 'show_section'));
        $this->assign('usernameSearch', $config->getValue('searchsys', 'username_search'));
        $this->assign('displayNameQuestion', $config->getValue('base', 'display_name_question'));
        $this->assign('onlineOnly', $config->getValue('searchsys', 'online_only_enabled'));
        $this->assign('withPhoto', $config->getValue('searchsys', 'with_photo_enabled'));
        
        $questions = $this->questionService->findAllQuestionsForAccountType($accountType);
        
        $section = null;
        $questionArray = array();

        foreach ( $questions as $sort => $question )
        {
            if ( !$question['onSearch'] )
            {
                continue;
            }
            
            if ( $section !== $question['sectionName'] )
            {
                $section = $question['sectionName'];
                $questionArray[$section] = array();
            }

            if ( isset($questions[$sort]['id']) )
            {
                $questionArray[$section][$sort] = $questions[$sort];
            }
        }

        $this->assign('questionsBySections', $questionArray);
        
        // -- Select account type form --
        $accountTypeSelectForm = new Form('qst_account_type_select_form');
        $accountTypeSelectForm->setMethod(Form::METHOD_GET);

        $qstAccountType = new Selectbox('accountType');
        $qstAccountType->addAttribute('id', 'qst_account_type_select');
        $qstAccountType->setLabel($lang->text('admin', 'questions_account_type_label'));
        $qstAccountType->setOptions($accounts);
        $qstAccountType->setValue($accountType);
        $qstAccountType->setHasInvitation(false);

        $accountTypeSelectForm->addElement($qstAccountType);

        $this->addForm($accountTypeSelectForm);

        $script = '$("#qst_account_type_select").change( function(){
            $(this).parents("form:eq(0)").submit();
        });';

        $this->addComponent('menu', $this->getMenu());

        PEEP::getDocument()->addOnloadScript($script);
        
        PEEP::getDocument()->setHeading($lang->text('searchsys', 'admin_page_heading'));
        
       
    }

    public function site()
    {
        $this->addComponent('menu', $this->getMenu());

        $service = SEARCHSYS_BOL_Service::getInstance();
        $lang = PEEP::getLanguage();
        $config = PEEP::getConfig();

        if ( PEEP::getRequest()->isPost() )
        {
            switch ( $_POST['action'] )
            {
                case 'update_groups':
                    $groups = $_POST['groups'];
                    $config->saveConfig('searchsys', 'site_search_groups', json_encode($groups));
                    break;

                case 'update_settings':
                    $config->saveConfig('searchsys', 'site_search_enabled', $_POST['site_search']);
                    break;
            }
            PEEP::getFeedback()->info($lang->text('searchsys', 'settings_updated'));
            $this->redirect();
        }

        $this->assign('groups', $service->getSiteSearchGroups());
        $this->assign('active', $service->getConfiguredGroupsForSiteSearch());
        $this->assign('searchEnabled', $config->getValue('searchsys', 'site_search_enabled'));

        PEEP::getDocument()->setHeading($lang->text('searchsys', 'admin_page_heading'));

       
    }

    private function getMenu()
    {
        $language = PEEP::getLanguage();
        $items = array();

        if ( !SEARCHSYS_BOL_Service::getInstance()->isPeepsys() )
        {
            $item = new BASE_MenuItem();
            $item->setLabel($language->text('searchsys', 'tab_user_search'));
            $item->setUrl(PEEP::getRouter()->urlForRoute('searchsys.admin-config'));
            $item->setKey('user');
            $item->setOrder(1);
            $item->setIconClass('peep_ic_user');
            
            array_push($items, $item);
        }

        $item2 = new BASE_MenuItem();
        $item2->setLabel($language->text('searchsys', 'tab_site_search'));
        $item2->setUrl(PEEP::getRouter()->urlForRoute('searchsys.admin-site-search'));
        $item2->setKey('site');
        $item2->setOrder(2);
        $item2->setIconClass('peep_ic_monitor');
        
        array_push($items, $item2);

        return new BASE_CMP_ContentMenu($items);
    }
}