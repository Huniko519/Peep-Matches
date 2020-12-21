<?php

class SPOTLIGHT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;

    public function __construct()
    {
        $this->service = SPOTLIGHT_BOL_Service::getInstance();

        parent::__construct();
    }

    public function index( $params = array() )
    {
        $userService = BOL_UserService::getInstance();

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('spotlight', 'admin_heading_settings'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');

        $settingsForm = new Form('settingsForm');
        $settingsForm->setId('settingsForm');

        $expiration_time = new TextField('expiration_time');
        $expiration_time->setRequired();
        $expiration_time->setLabel($language->text('spotlight', 'label_expiration_time'));
        $expiration_time_value = (int)PEEP::getConfig()->getValue('spotlight', 'expiration_time') / 86400;
        $expiration_time->setValue($expiration_time_value);

        $settingsForm->addElement($expiration_time);

        $submit = new Submit('save');
        $submit->addAttribute('class', 'peep_ic_save');
        $submit->setValue($language->text('spotlight', 'label_save_btn_label'));

        $settingsForm->addElement($submit);

        $this->addForm($settingsForm);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $settingsForm->isValid($_POST) )
            {
                $data = $settingsForm->getValues();

                
                PEEP::getConfig()->saveConfig('spotlight', 'expiration_time', $data['expiration_time']*86400);

                PEEP::getFeedback()->info($language->text('spotlight', 'settings_saved'));
                $this->redirect();
            }
        }
    }


}
