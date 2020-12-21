<?php

class BASE_CTRL_Preference extends PEEP_ActionController
{
    private $preferenceService;
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->preferenceService = BOL_PreferenceService::getInstance();
        $this->userService = BOL_UserService::getInstance();

        $contentMenu = new BASE_CMP_PreferenceContentMenu();
        $contentMenu->getElement('preference')->setActive(true);

        $this->addComponent('contentMenu', $contentMenu);
    }

    public function index( $params )
    {
        $userId = PEEP::getUser()->getId();

        if ( PEEP::getRequest()->isAjax() )
        {
            exit;
        }
        
        if ( !PEEP::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('base', 'preference_index'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');

        // -- Preference form --
        
        $preferenceForm = new Form('preferenceForm');
        $preferenceForm->setId('preferenceForm');

        $preferenceSubmit = new Submit('preferenceSubmit');
        $preferenceSubmit->addAttribute('class', 'peep_button peep_ic_save');

        $preferenceSubmit->setValue($language->text('base', 'preference_submit_button'));
        
        $preferenceForm->addElement($preferenceSubmit);

        // --

        $sectionList = BOL_PreferenceService::getInstance()->findAllSections();
        $preferenceList = BOL_PreferenceService::getInstance()->findAllPreference();

        $preferenceNameList = array();
        foreach( $preferenceList as $preference )
        {
            $preferenceNameList[$preference->key] = $preference->key;
        }

        $preferenceValuesList = BOL_PreferenceService::getInstance()->getPreferenceValueListByUserIdList($preferenceNameList, array($userId));

        $formElementEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array( 'values' => $preferenceValuesList[$userId] ) );
        PEEP::getEventManager()->trigger($formElementEvent);
        $data = $formElementEvent->getData();
        
        $formElements = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $formElementList = array();

        foreach( $formElements as $formElement )
        {
            /* @var $formElement FormElement */

            $formElementList[$formElement->getName()] = $formElement;
        }
        
        $resultList = array();

        foreach( $sectionList as $section )
        {
            foreach( $preferenceList as $preference )
            {
                if( $preference->sectionName === $section->name && !empty( $formElementList[$preference->key] ) )
                {
                    $resultList[$section->name][$preference->key] = $preference->key;

                    $element = $formElementList[$preference->key];
                    $preferenceForm->addElement($element);
                }
            }
        }

        if ( PEEP::getRequest()->isPost() )
        {
            if( $preferenceForm->isValid($_POST) )
            {
                $values = $preferenceForm->getValues();
                $restul = BOL_PreferenceService::getInstance()->savePreferenceValues($values, $userId);

                if ( $restul )
                {
                    PEEP::getFeedback()->info($language->text('base', 'preference_preference_data_was_saved'));
                }
                else
                {
                    PEEP::getFeedback()->warning($language->text('base', 'preference_preference_data_not_changed'));
                }
                
                $this->redirect();
            }
        }

        $this->addForm($preferenceForm);
$adminMode = false;
$this->assign('unregisterProfileUrl', PEEP::getRouter()->urlForRoute('base_delete_user'));
$this->assign('isAdmin', PEEP::getUser()->isAdmin());
        $data = array();
        $sectionLabelEvent = new BASE_CLASS_EventCollector( BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT );
        PEEP::getEventManager()->trigger($sectionLabelEvent);
        $data = $sectionLabelEvent->getData();
        
        $sectionLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        $this->assign('preferenceList', $resultList);
        $this->assign('sectionLabels', $sectionLabels);
    }


}