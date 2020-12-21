<?php

class GOOGLEAUTH_CTRL_Admin extends ADMIN_CTRL_Abstract
{
	public function __construct() {
        parent::__construct();
    }
	public function index(){	

		$form = new GOOGLEAUTH_AccessForm();
		$this->addForm($form);
	

		if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) ){
	
			if ( $form->process() ){
				PEEP::getFeedback()->info(PEEP::getLanguage()->text('googleauth', 'register_app_success'));
				$this->redirect(PEEP::getRouter()->urlForRoute('googleauth_app_success_page'));
			}
		
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('googleauth', 'register_app_failed'));
			$this->redirect();
		}  
		$this->assign('returnUrl',PEEP::getRouter()->urlForRoute('googleauth_oauth'));
		PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('googleauth', 'heading_configuration'));
        PEEP::getDocument()->setHeadingIconClass('peep_ic_friends');
	}
	
	public function success() {
		PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('googleauth', 'heading_configuration'));
		$success_text = PEEP::getLanguage()->text('googleauth','register_success_msg');
		$this->assign('text', $success_text);
	}
    
}


class GOOGLEAUTH_AccessForm extends Form {

  public function __construct()
  {
    parent::__construct('GOOGLEAUTH_AccessForm');
    $service = GOOGLEAUTH_BOL_Service::getInstance();
    $conf = $service->getProperties();
    $field = new TextField('clientId');
    $field->setRequired(true);
    $field->setValue($conf->client_id);
    $this->addElement($field);

    $field = new TextField('clientSecret');
    $field->setRequired(true);
    $field->setValue($conf->client_secret);
    $this->addElement($field);

    $submit = new Submit('save');
    $submit->setValue(PEEP::getLanguage()->text('googleauth', 'save_btn_label'));
    $this->addElement($submit);
  }

  public function process()
  {
    $values = $this->getValues();
    $service = GOOGLEAUTH_BOL_Service::getInstance();
    $conf = new GOOGLEAUTH_BOL_Config();
    $conf->client_id = trim($values['clientId']);
    $conf->client_secret = trim($values['clientSecret']);
    return $service->saveProperties($conf);
  }
}
