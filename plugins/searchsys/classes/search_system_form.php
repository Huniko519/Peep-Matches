<?php

class SEARCHSYS_CLASS_SearchSystemForm extends BASE_CLASS_UserQuestionForm
{
    const SUBMIT_NAME = 'SearchSystemFormSubmit';

    const FORM_SESSION_VAR = 'MAIN_SEARCH_FORM_DATA';

    public $component;
    public $accountType;
    public $displayAccountType = false;

    /**
     * @param PEEP_Component $component
     */
    public function __construct( PEEP_Component $component )
    {
        parent::__construct('SearchSystemForm');

        $this->component = $component;
        
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(PEEP::getRouter()->urlForRoute('searchsys.search-action'));
        
        $questionService = BOL_QuestionService::getInstance();
        $config = PEEP::getConfig();
        $language = PEEP::getLanguage();

        $this->setId('SearchSystemForm');

        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue($language->text('base', 'user_search_submit_button_label'));
        $this->addElement($submit);

        $questionData = PEEP::getSession()->get(self::FORM_SESSION_VAR);

        if ( $questionData === null )
        {
            $questionData = array();
        }

        $accounts = $this->getAccountTypes();

        $accountList = array();
        if ( count($accounts) > 1 )
        {
            $accountList[BOL_QuestionService::ALL_ACCOUNT_TYPES] = $language->text('base', 'questions_account_type_' . BOL_QuestionService::ALL_ACCOUNT_TYPES);
        }

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
            $accountType->setLabel($language->text('base', 'questions_question_account_type_label'));
            $accountType->setRequired();
            $accountType->setOptions($accountList);
            $accountType->setValue($this->accountType);
            $accountType->setHasInvitation(false);

            $this->addElement($accountType);
        }

        if ( $config->getValue('searchsys', 'online_only_enabled') )
        {
            $onlineOnly = new CheckboxField('onlineOnly');
            $onlineOnly->setLabel($language->text('searchsys', 'online_only'));
            if ( isset($questionData['onlineOnly']) ) 
            {
                $onlineOnly->setValue($questionData['onlineOnly']);
            }
            
            $this->addElement($onlineOnly);
        }

        if ( $config->getValue('searchsys', 'with_photo_enabled') )
        {
            $withPhoto = new CheckboxField('withPhoto');
            $withPhoto->setLabel($language->text('searchsys', 'with_photo'));
            if ( isset($questionData['withPhoto']) )
            {
                $withPhoto->setValue($questionData['withPhoto']);
            }

            $this->addElement($withPhoto);
        }

        $questions = $questionService->findSearchQuestionsForAccountType($this->accountType);
        $conf = SEARCHSYS_BOL_Service::getInstance()->getConfiguredQuestionsForAccountType($this->accountType, $accounts);
        $conf = array_keys($conf);

        // check if search by username enabled
        if ( $config->getValue('searchsys', 'username_search') )
        {
            $questionName = PEEP::getConfig()->getValue('base', 'display_name_question');

            $unsetField = null;
            foreach ( $questions as $key => $question )
            {
                if ( $question['name'] == $questionName )
                {
                    $unsetField = $key;
                    break;
                }
            }

            if ( $unsetField !== null )
            {
                unset($questions[$unsetField]);
            }

            $question = (array)$questionService->findQuestionByName($questionName);
            array_unshift($questions, $question);
            array_push($conf, $questionName);
        }

        $qSearchQuestion = array();
        $questionNameList = array();
        
        foreach ( $questions as $key => $question )
        {
            if ( !in_array($question['name'], $conf) )
            {
                unset($questions[$key]);
                continue;
            }
            
            $sectionName = $question['sectionName'];
            $qSearchQuestion[$sectionName][] = $question;
            $questionNameList[] = $question['name'];
            $questions[$key]['required'] = '0';
        }

        $questionValueList = $questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addQuestions($questions, $questionValueList, $questionData);

        $component->assign('questionList', $qSearchQuestion);
        $component->assign('displayAccountType', $this->displayAccountType);
        
        if ( $this->displayAccountType )
        {
            $script = 
            '$("form[name=SearchSystemForm] select[name=accountType]").change(
                function(){
                  var $select = $(this);
                  var $container = $select.closest(".qsearch_user_search_cmp");
                  $("tr:not(.peep_tr_first)", $container).hide();
                  PEEP.inProgressNode($container.find(".peep_button input"));
                  
                  $.ajax({
                    url: '.json_encode(PEEP::getRouter()->urlForRoute('searchsys.set-acc-type')).',
                    type: "POST",
                    data: { accType: $select.val() },
                    dataType: "json",
                    success: function(data) {
                        if ( data.result == true ) {
                            PEEP.loadComponent("SEARCHSYS_CMP_UserSearch", { },
                            {
                                onReady: function( html ){
                                    $container.empty().html(html);
                                    PEEP.activateNode($container.find(".peep_button input"));
                                }
                            });
                        }
                    }
                  });    
                }
            );';
            PEEP::getDocument()->addOnloadScript($script);
        }

        $this->bindJsFunction(Form::BIND_SUCCESS, "
            function(data){
                if ( data.result ) {
                    document.location.href = data.url;
                }
                else if ( data.error ) {
                    PEEP.error(data.error);        
                }
            }"
        );
    }

    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getSearchPresentationClass($presentation, $questionName, $configs);
    }
}