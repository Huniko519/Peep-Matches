<?php
/* Peepmatches Light By Peepdev co */


class ADMIN_CTRL_PagesEditLocal extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');
        PEEP::getDocument()->getMasterPage()->getMenu(PEEP_Navigation::ADMIN_PAGES)->getElement('sidebar_menu_item_pages_manage')->setActive(true);
        $this->assign('homeUrl', PEEP::getRouter()->getBaseUrl());
    }

    public function index( $params )
    {
        $id = (int) $params['id'];

        $this->assign('id', $id);

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        if ( $menu === null )
        {
            throw new Redirect404Exception();
        }

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        if ( $document === null )
        {
            $document = new BOL_Document();
            $document->setKey($menu->getDocumentKey());
            $document->setIsStatic(true);
        }

        $service = BOL_NavigationService::getInstance();

        $form = new EditLocalPageForm('edit-form', $menu);

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
//--
            $visibleFor = 0;
            $arr = !empty($data['visible-for']) ? $data['visible-for'] : array();
            foreach ( $arr as $val )
            {
                $visibleFor += $val;
            }

            $service->saveMenuItem(
                $menu->setVisibleFor($visibleFor)
            );

            $uri = str_replace(UTIL_String::removeFirstAndLastSlashes(PEEP::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($data['url']));
            $document->setUri(UTIL_String::removeFirstAndLastSlashes($uri));

            $navigationService->saveDocument($document);

            $languageService = BOL_LanguageService::getInstance();

            $plugin = PEEP::getPluginManager()->getPlugin('base');

//- name
            $langKey = $languageService->findKey(
                    $plugin->getKey(), $menu->getKey()
            );
            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }

                $languageService->saveValue(
                    $langValue->setValue($data['name'])
                );
            }
//- title

            $langKey = $languageService->findKey(
                    $plugin->getKey(), 'local_page_title_' . $menu->getKey()
            );


            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue(
                        $languageService->getCurrent()->getId(), $langKey->getId()
                );

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }

                $languageService->saveValue(
                    $langValue->setValue($data['title'])
                );
            }
//- meta tags

            $langKey = $languageService->findKey($plugin->getKey(), 'local_page_meta_tags_' . $menu->getKey());

            if ( empty($langKey) )
            {
                $langKey = new BOL_LanguageKey();
                $langKey->setKey('local_page_meta_tags_' . $menu->getKey());
                $langKey->setPrefixId($languageService->findPrefixId($plugin->getKey()));

                $languageService->saveKey($langKey);
            }


            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            if ( $langValue === null )
            {
                $langValue = new BOL_LanguageValue();
                $langValue->setKeyId($langKey->getId());
                $langValue->setLanguageId($languageService->getCurrent()->getId());
            }

            $languageService->saveValue($langValue->setValue($data['meta-tags']));

//- content

            $langKey = $languageService->findKey(
                    $plugin->getKey(), 'local_page_content_' . $menu->getKey()
            );

            if ( !empty($langKey) )
            {
                $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

                if ( $langValue === null )
                {
                    $langValue = new BOL_LanguageValue();
                    $langValue->setKeyId($langKey->getId());
                    $langValue->setLanguageId($languageService->getCurrent()->getId());
                }

                $languageService->saveValue(
                    $langValue->setValue($data['content'])
                );
            }

//~
            $languageService->generateCache($languageService->getCurrent()->getId());

            $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

            PEEP::getFeedback()->info(PEEP::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();

//--
        }

        $this->addForm($form, $menu);
    }

    public function delete( $params )
    {
        $id = 0;

        if ( empty($params['id'])
            || ( $id = (int) $params['id'] ) <= 0
        )
        {
            exit();
        }

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        $navigationService->deleteDocument($document);

        $languageService = BOL_LanguageService::getInstance();

        $navigationService->deleteMenuItem($menu);

        $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());
        $languageService->deleteKey($langKey->getId());

        $langKey = $languageService->findKey('base', 'local_page_meta_tags_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $langKey = $languageService->findKey('base', 'local_page_title_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $langKey = $languageService->findKey('base', 'local_page_content_' . $document->getKey());
        if ( $langKey !== null )
        {
            $languageService->deleteKey($langKey->getId());
        }

        $this->redirect(PEEP::getRouter()->urlForRoute('admin_pages_main'));
    }
}

class EditLocalPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        if ( $document === null )
        {
            $document = new BOL_Document();
            $document->setKey($menu->getDocumentKey());
        }

        $language = PEEP_Language::getInstance();
        $languageService = BOL_LanguageService::getInstance();
        $currentLanguageId = $languageService->getCurrent()->getId();

        $plugin = PEEP::getPluginManager()->getPlugin('base');
        $adminPlugin = PEEP::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), $menu->getKey());
        $this->addElement(
                $nameTextField->setValue($langValue)
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_menu_name'))
                ->setRequired()
        );

        $titleTextField = new TextField('title');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_title_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_title_' . $menu->getKey());
        $this->addElement(
                $titleTextField->setValue($langValue)
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_page_title'))
                ->setRequired(true)
        );

        $urlTextField = new TextField('url');
        $urlTextField->addValidator(new LocalPageUniqueValidator($document->getUri()));

        $this->addElement(
                $urlTextField->setValue($document->getUri())
                ->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_page_url'))
                ->setRequired(true)
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

        $metaTagsTextarea = new Textarea('meta-tags');

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_meta_tags_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_meta_tags_' . $menu->getKey());
        $this->addElement(
                $metaTagsTextarea->setLabel('Page meta tags')
                ->setValue($langValue)
                ->setDescription(PEEP::getLanguage()->text('admin', 'pages_page_field_meta_desc'))
                ->setId('meta-tags')
        );

        $contentTextArea = new Textarea('content');

        $contentTextArea->setDescription(
            PEEP::getLanguage()->text('admin', 'pages_page_field_content_desc', array(
                'src' => PEEP::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'question.png',
                'url' => '#'
                )
            )
        );

        $langValueDto = $languageService->getValue($currentLanguageId, $plugin->getKey(), 'local_page_content_' . $menu->getKey());
        $langValue = $langValueDto === null ? '' : $language->text($plugin->getKey(), 'local_page_content_' . $menu->getKey());
        $this->addElement(
                $contentTextArea->setLabel(PEEP::getLanguage()->text('admin', 'pages_edit_local_page_content'))
                ->setValue($langValue)
                ->setId('content')
        );


        $saveSubmit = new Submit('save');

        $this->addElement(
            $saveSubmit->setValue($language->text($adminPlugin->getKey(), 'save_btn_label'))
        );
    }
}

class LocalPageUniqueValidator extends PEEP_Validator
{
    private $uri;

    public function __construct( $uri )
    {
        $this->uri = $uri;
        $this->setErrorMessage(PEEP::getLanguage()->text('base', 'unique_local_page_error'));
    }

    public function isValid( $value )
    {
        $value = str_replace(UTIL_String::removeFirstAndLastSlashes(PEEP::getRouter()->getBaseUrl()), '', UTIL_String::removeFirstAndLastSlashes($value));

        if ( !trim($value) )
        {
            return false;
        }

        return ( $this->uri == $value || BOL_NavigationService::getInstance()->isDocumentUriUnique($value) );
    }
}