<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_PagesEditExternal extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
        PEEP::getDocument()->getMasterPage()->getMenu(PEEP_Navigation::ADMIN_PAGES)->getElement('sidebar_menu_item_pages_manage')->setActive(true);
    }

    public function delete( $params )
    {

        $id = (int) $params['id'];

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $service = BOL_NavigationService::getInstance();

        $languageService = BOL_LanguageService::getInstance();

        $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());

        if ( !empty($langKey) )
        {
            $list = $languageService->findAll();

            foreach ( $list as $dto )
            {
                $langValue = $languageService->findValue($dto->getId(), $langKey->getId());

                if ( empty($langValue) )
                {
                    continue;
                }

                $languageService->deleteValue($langValue);
            }

            $languageService->deleteKey($langKey->getId());
        }

        $service->deleteMenuItem($menu);

        $this->redirect(PEEP::getRouter()->urlForRoute('admin_pages_main'));
    }

    public function index( $params )
    {

        $id = (int) $params['id'];

        $this->assign('id', $id);

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $service = BOL_NavigationService::getInstance();

        $form = new EditExternalPageForm('edit-form', $menu);

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
                    $menu->setExternalUrl($data['url'])
                    ->setVisibleFor($visibleFor)
                    ->setNewWindow((!empty($_POST['ext-open-in-new-window']) && $_POST['ext-open-in-new-window'] == 'on'))
            );

            $languageService = BOL_LanguageService::getInstance();

            $plugin = PEEP::getPluginManager()->getPlugin('base');

            $langKey = $languageService->findKey($plugin->getKey(), $menu->getKey());

            if ( empty($langKey) )
            {
                $langPrefixDto = $languageService->findPrefix($menu->getPrefix());
                $langKey = $languageService->addKey($langPrefixDto->getId(), $menu->getKey());
            }

            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            if ( empty($langValue) )
            {
                $languageService->addValue($languageService->getCurrent()->getId(), $menu->getPrefix(), $langKey->getKey(), $data['name']);
            }
            else
            {
                $languageService->saveValue(
                    $langValue->setValue($data['name'])
                );
            }

            $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

            PEEP::getFeedback()->info(PEEP::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();
        }

        $this->addForm($form);
    }
}

class EditExternalPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $language = PEEP_Language::getInstance();

        $plugin = PEEP::getPluginManager()->getPlugin('base');
        $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $this->addElement(
                $nameTextField->setValue($language->text($plugin->getKey(), $menu->getKey()))
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_external_menu_name_label'))
                ->setRequired(true)
        );

        $urlTextField = new TextField('url');

        $urlTextField->addValidator( new ADMIN_CLASS_ExternalPageUrlValidator() );

        $this->addElement(
                $urlTextField->setValue($menu->getExternalUrl())
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_external_url_label'))
                ->setRequired(true)
        );

        $extOpenInNewWindow = new CheckboxField('ext-open-in-new-window');

        $this->addElement(
                $extOpenInNewWindow->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_external_url_open_in_new_window'))
                ->setValue($menu->getNewWindow())
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
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_external_visible_for'))
        );



        $saveSubmit = new Submit('save');

        $this->addElement(
            $saveSubmit->setValue($language->text($adminPlugin->getKey(), 'save_btn_label'))
        );
    }
}