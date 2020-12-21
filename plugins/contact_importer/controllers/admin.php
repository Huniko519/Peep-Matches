<?php

class CONTACTIMPORTER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function admin()
    {
        $event = new BASE_CLASS_EventCollector(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS);
        PEEP::getEventManager()->trigger($event);
        $providers = $event->getData();
        $firstProvider = reset($providers);

        $this->redirect($firstProvider['settigsUrl']);
    }

    public function facebook( $params )
    {
        $this->addComponent('menu', new CONTACTIMPORTER_CMP_AdminTabs());

        $appId = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_id');
        $appSecret = PEEP::getConfig()->getValue('contactimporter', 'facebook_app_secret');

        $form = new Form('fasebook_settings');

        $element = new TextField('appId');
        $element->setLabel(PEEP::getLanguage()->text('contactimporter', 'facebook_app_id'));
        $element->setRequired(true);
        $element->setValue($appId);
        $form->addElement($element);

        $element = new TextField('appSecret');
        $element->setLabel(PEEP::getLanguage()->text('contactimporter', 'facebook_app_secret'));
        $element->setRequired(true);
        $element->setValue($appSecret);
        $form->addElement($element);

        $element = new Submit('save');
        $element->setValue(PEEP::getLanguage()->text('contactimporter', 'save_btn_label'));

        $form->addElement($element);

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $value = trim($form->getElement('appId')->getValue());
            PEEP::getConfig()->saveConfig('contactimporter', 'facebook_app_id', $value);

            $value = trim($form->getElement('appSecret')->getValue());
            PEEP::getConfig()->saveConfig('contactimporter', 'facebook_app_secret', $value);

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('contactimporter', 'admin_settings_updated'));

            $this->redirect();
        }

        $this->addForm($form);

        $manualUrl = 'http://developers.facebook.com/';


        

        $this->assign('manualUrl', $manualUrl);
    }


    public function google( $params )
    {
        $this->addComponent('menu', new CONTACTIMPORTER_CMP_AdminTabs());

        $clientId = PEEP::getConfig()->getValue('contactimporter', 'google_client_id');
        $clientSecret = PEEP::getConfig()->getValue('contactimporter', 'google_client_secret');

        $form = new Form('google_settings');

        $element = new TextField('clientId');
        $element->setLabel(PEEP::getLanguage()->text('contactimporter', 'google_client_id'));
        $element->setRequired(true);
        $element->setValue($clientId);
        $form->addElement($element);

        $element = new TextField('clientSecret');
        $element->setLabel(PEEP::getLanguage()->text('contactimporter', 'google_client_secret'));
        $element->setRequired(true);
        $element->setValue($clientSecret);
        $form->addElement($element);

        $element = new Submit('save');
        $element->setValue(PEEP::getLanguage()->text('contactimporter', 'save_btn_label'));

        $form->addElement($element);

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            PEEP::getConfig()->saveConfig('contactimporter', 'google_client_id', trim($form->getElement('clientId')->getValue()));
            PEEP::getConfig()->saveConfig('contactimporter', 'google_client_secret', trim($form->getElement('clientSecret')->getValue()));

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('contactimporter', 'admin_settings_updated'));

            $this->redirect();
        }

        $this->addForm($form);

        $manualUrl = 'https://console.developers.google.com/project?authuser=0';

       
        $this->assign('manualUrl', $manualUrl);
    }
}
