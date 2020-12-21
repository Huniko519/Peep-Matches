<?php

class PVISITORS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private function getMenu()
    {
        $language = PEEP::getLanguage();
        $menuItems = array();
        
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('pvisitors', 'general_settings'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('pvisitors.admin'));
        $item->setKey('settings');
        $item->setIconClass('peep_ic_gear_wheel');
        $item->setOrder(0);

        array_push($menuItems, $item);
        
        $menu = new BASE_CMP_ContentMenu($menuItems);

        return $menu;
    }
    
    /**
     * Default action
     */
    public function index()
    {
    	$lang = PEEP::getLanguage();
        
        $form = new FormConfig();
        $this->addForm($form);
        
        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
        	$values = $form->getValues();
        	if ( $values['months'] > 12 )
        	{
                $values['months'] = 12;
        	}
        	
        	PEEP::getConfig()->saveConfig('pvisitors', 'store_period', (int) $values['months']);

        	PEEP::getFeedback()->info($lang->text('pvisitors', 'settings_updated'));
        	$this->redirect();
        }
        
        $this->addComponent('menu', $this->getMenu());
        
        $form->getElement('months')->setValue(PEEP::getConfig()->getValue('pvisitors', 'store_period'));
        
        
        
        $this->setPageHeading($lang->text('pvisitors', 'page_heading_admin'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
    }
}

class FormConfig extends Form 
{
    public function __construct()
    {
        parent::__construct('config-form');
        
        $lang = PEEP::getLanguage();
        
        $months = new TextField('months');
        $months->setRequired(true);
        $months->addValidator(new IntValidator(1, 12));
        $months->setLabel($lang->text('pvisitors', 'store_period'));
        $this->addElement($months);
        
        $submit = new Submit('save');
        $submit->setLabel($lang->text('pvisitors', 'save'));
        $this->addElement($submit);
    }
}