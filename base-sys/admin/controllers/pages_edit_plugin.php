<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_PagesEditPlugin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
        PEEP::getDocument()->getMasterPage()->getMenu(PEEP_Navigation::ADMIN_PAGES)->getElement('sidebar_menu_item_pages_manage')->setActive(true);
    }

    public function index( $params )
    {
        $id = (int) $params['id'];

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $form = new EditPluginPageForm('edit-form', $menu);

        $service = BOL_NavigationService::getInstance();

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            $visibleFor = 0;
            $arr = !empty($data['visible-for']) ? $data['visible-for'] : array();
            foreach ( $arr as $val )
            {
                $visibleFor += $val;
            }

            $service->saveMenuItem(
                $menu->setVisibleFor($visibleFor)
            );

            $languageService = BOL_LanguageService::getInstance();


            $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());

            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            $languageService->saveValue(
                $langValue->setValue($data['name'])
            );

            $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

            PEEP::getFeedback()->info(PEEP::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();
        }

//--    	
        $this->addForm($form);
    }
}

class EditPluginPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        $language = PEEP_Language::getInstance();

        $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $this->addElement(
                $nameTextField->setValue($language->text($menu->getPrefix(), $menu->getKey()))
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_menu_name'))
                ->setRequired()
        );

        $visibleForCheckboxGroup = new CheckboxGroup('visible-for');

        $visibleFor = $menu->getVisibleFor();

        $options = array(
            '1' => PEEP::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
            '2' => PEEP::getLanguage()->text('admin', 'pages_edit_visible_for_members')
        );

        $values = array();

        foreach ( $options as $value => $option )
        {
            if ( !($value & $visibleFor) )
                continue;

            $values[] = $value;
        }

        $this->addElement(
                $visibleForCheckboxGroup->setOptions($options)
                ->setValue($values)
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_visible_for'))
        );

        $submit = new Submit('save');

        $this->addElement(
            $submit->setValue(PEEP::getLanguage()->text('admin', 'save_btn_label'))
        );
    }
}