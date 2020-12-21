<?php

class CNEWS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * Default action
     */
    public function index()
    {
        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('cnews', 'admin_page_heading'));
        $this->setPageTitle($language->text('cnews', 'admin_page_title'));
        $this->setPageHeadingIconClass('peep_ic_comment');

        $configs = PEEP::getConfig()->getValues('cnews');
        $this->assign('configs', $configs);

        $form = new CNEWS_ConfigSaveForm($configs);

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if ( $form->process($_POST) )
            {
                PEEP::getFeedback()->info($language->text('cnews', 'settings_updated'));
                $this->redirect(PEEP::getRouter()->urlForRoute('cnews_admin_settings'));
            }
        }

        $this->addComponent('menu', $this->getMenu());
    }

    public function customization()
    {
        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('cnews', 'admin_page_heading'));
        $this->setPageTitle($language->text('cnews', 'admin_page_title'));
        $this->setPageHeadingIconClass('peep_ic_comment');

        $types = CNEWS_BOL_CustomizationService::getInstance()->getActionTypes();

        $form = new CNEWS_CustomizationForm();
        $this->addForm($form);

        $processTypes = array();

        foreach ( $types as $type )
        {
            $field = new CheckboxField($type['activity']);
            $field->setValue($type['active']);
            $form->addElement($field);

            $processTypes[] = $type['activity'];
        }

        if ( PEEP::getRequest()->isPost() )
        {
            $result = $form->process($_POST, $processTypes);
            if ( $result )
            {
                PEEP::getFeedback()->info($language->text('cnews', 'customization_changed'));
            }
            else
            {
                PEEP::getFeedback()->warning($language->text('cnews', 'customization_not_changed'));
            }

            $this->redirect();
        }

        $this->assign('types', $types);
        $this->addComponent('menu', $this->getMenu());
    }

    private function getMenu()
    {
        $language = PEEP::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('cnews', 'admin_menu_item_settings'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('cnews_admin_settings'));
        $item->setKey('cnews_settings');
        $item->setIconClass('peep_ic_gear_wheel');
        $item->setOrder(0);

        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('cnews', 'admin_menu_item_customization'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('cnews_admin_customization'));
        $item->setKey('cnews_customization');
        $item->setIconClass('peep_ic_files');
        $item->setOrder(1);

        $menuItems[] = $item;

        return new BASE_CMP_ContentMenu($menuItems);
    }
}

/**
 * Save photo configuration form class
 */
class CNEWS_ConfigSaveForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct( $configs )
    {
        parent::__construct('CNEWS_ConfigSaveForm');

        $language = PEEP::getLanguage();

        $field = new CheckboxField('allow_comments');
        $field->setLabel($language->text('cnews', 'admin_allow_comments_label'));
        $field->setValue($configs['allow_comments']);
        $this->addElement($field);

        $field = new CheckboxField('features_expanded');
        $field->setLabel($language->text('cnews', 'admin_features_expanded_label'));
        $field->setValue($configs['features_expanded']);
        $this->addElement($field);

        $field = new CheckboxField('index_status_enabled');
        $field->setLabel($language->text('cnews', 'admin_index_status_label'));
        $field->setValue($configs['index_status_enabled']);
        $this->addElement($field);

        $field = new CheckboxField('allow_likes');
        $field->setLabel($language->text('cnews', 'admin_allow_likes_label'));
        $field->setValue($configs['allow_likes']);
        $this->addElement($field);

        $field = new TextField('comments_count');
        $field->setValue($configs['comments_count']);
        $field->setRequired(true);
        $validator = new IntValidator();
        $field->addValidator($validator);
        $field->setLabel($language->text('cnews', 'admin_comments_count_label'));
        $this->addElement($field);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('cnews', 'admin_save_btn'));
        $this->addElement($submit);
    }

    /**
     * Updates photo plugin configuration
     *
     * @return boolean
     */
    public function process( $data )
    {
        $config = PEEP::getConfig();

        $config->saveConfig('cnews', 'allow_likes', $data['allow_likes']);
        $config->saveConfig('cnews', 'allow_comments', $data['allow_comments']);
        $config->saveConfig('cnews', 'comments_count', $data['comments_count']);
        $config->saveConfig('cnews', 'features_expanded', $data['features_expanded']);
        $config->saveConfig('cnews', 'index_status_enabled', $data['index_status_enabled']);

        return true;
    }
}

class CNEWS_CustomizationForm extends Form
{

    public function __construct(  )
    {
        parent::__construct('CNEWS_CustomizationForm');

        $language = PEEP::getLanguage();

        $btn = new Submit('save');
        $btn->setValue($language->text('cnews', 'save_customization_btn_label'));
        $this->addElement($btn);
    }

    public function process( $data, $types )
    {
        $changed = false;
        $configValue = json_decode(PEEP::getConfig()->getValue('cnews', 'disabled_action_types'), true);
        $typesToSave = array();

        foreach ( $types as $type )
        {
            $typesToSave[$type] = isset($data[$type]);
            if ( !isset($configValue[$type]) || $configValue[$type] !== $typesToSave[$type] )
            {
                $changed = true;
            }
        }

        $jsonValue = json_encode($typesToSave);
        PEEP::getConfig()->saveConfig('cnews', 'disabled_action_types', $jsonValue);

        return $changed;
    }
}