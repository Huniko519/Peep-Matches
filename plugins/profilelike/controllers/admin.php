<?php
class PROFILELIKE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private function setMenu()
    {
        $language = PEEP::getLanguage();  
		$menuItems = array();		
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('profilelike', 'general_settings'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('profilelike.admin'));
        $item->setKey('settings');
        $item->setOrder(0);
		array_push($menuItems, $item);
        $menu = new BASE_CMP_ContentMenu($menuItems);
        return $menu;
    }
	
	public function index()
	{
		$service = PROFILELIKE_BOL_Service::getInstance();
		$language = PEEP::getLanguage();
		
		$this->addComponent('menu', $this->setMenu());
		$this->setPageHeading($language->text('profilelike', 'admin_plugin_heading'));
		
		$form = new Form('form-config');
        $this->addForm($form);
		
		$counter = new TextField('counter1');
        $counter->setRequired(true);
        $counter->addValidator(new IntValidator(1, 12));
        $counter->setLabel($language->text('profilelike', 'thumbnails_in_profile_widget'));
        $form->addElement($counter);
		$form->getElement('counter1')->setValue(PEEP::getConfig()->getValue('profilelike', 'thumbnails_in_profile_widget'));
		
		$counter = new TextField('counter2');
        $counter->setRequired(true);
        $counter->addValidator(new IntValidator(1, 12));
        $counter->setLabel($language->text('profilelike', 'thumbnails_in_dashboard_widget'));
        $form->addElement($counter);
		$form->getElement('counter2')->setValue(PEEP::getConfig()->getValue('profilelike', 'thumbnails_in_dashboard_widget'));
		
		$submit = new Submit('save');
        $submit->setLabel($language->text('profilelike', 'button_save'));
        $form->addElement($submit);
		
		
		if (PEEP::getRequest()->isPost() && $form->isValid($_POST))
        {
        	$formValues = $form->getValues();
        	PEEP::getConfig()->saveConfig('profilelike', 'thumbnails_in_profile_widget', (int) $formValues['counter1']);
			PEEP::getConfig()->saveConfig('profilelike', 'thumbnails_in_dashboard_widget', (int) $formValues['counter2']);
        	PEEP::getFeedback()->info($language->text('profilelike', 'settings_saved'));
        	$this->redirect();
        }
	}
}
