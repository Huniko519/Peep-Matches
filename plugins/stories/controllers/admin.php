<?php

class STORIES_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'admin_stories_settings_heading'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        $form = new SettingsForm($this);
        if ( !empty($_POST) && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            PEEP::getConfig()->saveConfig('stories', 'results_per_page', $data['results_per_page']);
        }

        $this->addForm($form);
    }
    
    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            PEEP::getConfig()->saveConfig('stories', 'uninstall_inprogress', 1);

            //maint-ce mode

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('stories', 'plugin_set_for_uninstall'));
            $this->redirect();
        }

        $this->setPageHeading(PEEP::getLanguage()->text('stories', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('peep_ic_delete');

        $this->assign('inprogress', (bool) PEEP::getConfig()->getValue('stories', 'uninstall_inprogress'));

        $js = new UTIL_JsGenerator();

        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.PEEP::getLanguage()->text('stories', 'confirm_delete_photos').'") ) return false;');

        PEEP::getDocument()->addOnloadScript($js);    	
    }    

}

class SettingsForm extends Form
{

    public function __construct( $ctrl )
    {
        parent::__construct('form');

        $configs = PEEP::getConfig()->getValues('stories');

        $ctrl->assign('configs', $configs);

        $l = PEEP::getLanguage();

        $textField['results_per_page'] = new TextField('results_per_page');

        $textField['results_per_page']->setLabel($l->text('stories', 'settings_results_per_page'))
            ->setValue($configs['results_per_page'])
            ->addValidator(new IntValidator())
            ->setRequired(true);

        $this->addElement($textField['results_per_page']);

        $submit = new Submit('submit');

        $submit->setValue($l->text('stories', 'save_btn_label'));

        $this->addElement($submit);
    }
}