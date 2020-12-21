<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Theme extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_ThemeService
     *
     */
    private $themeService;
    /**
     * @var BASE_CMP_ContentMenu
     */
    private $menu;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->themeService = BOL_ThemeService::getInstance();
        $this->setDefaultAction('settings');
    }

    public function init()
    {
        $router = PEEP_Router::getInstance();

        $pageActions = array(array('name' => 'settings', 'iconClass' => 'peep_ic_gear_wheel'), array('name' => 'css', 'iconClass' => 'peep_ic_files'));

        $menuItems = array();

        foreach ( $pageActions as $key => $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($item['name'])->setLabel(PEEP::getLanguage()->text('admin', 'sidebar_menu_item_' . $item['name']))->setOrder($key)->setUrl($router->urlForRoute('admin_theme_' . $item['name']));
            $menuItem->setIconClass($item['iconClass']);
            $menuItems[] = $menuItem;
        }

        $this->menu = new BASE_CMP_ContentMenu($menuItems);

        $this->addComponent('contentMenu', $this->menu);

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_APPEARANCE, 'admin', 'sidebar_menu_item_theme_edit');
        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'themes_settings_page_title'));
    }

    public function settings()
    {
        $dto = $this->themeService->findThemeByName(PEEP::getConfig()->getValue('base', 'selectedTheme'));

        if ( $dto === null )
        {
            throw new LogicException("Can't find theme `" . PEEP::getConfig()->getValue('base', 'selectedTheme') . "`");
        }

        $assignArray = (array) json_decode($dto->getDescription());

        $assignArray['iconUrl'] = $this->themeService->getStaticUrl($dto->getName()) . BOL_ThemeService::ICON_FILE;
        $assignArray['name'] = $dto->getName();
        $assignArray['title'] = $dto->getTitle();
        $this->assign('themeInfo', $assignArray);
        $this->assign('resetUrl', PEEP::getRouter()->urlFor(__CLASS__, 'reset'));

        $controls = $this->themeService->findThemeControls($dto->getId());

        if ( empty($controls) )
        {
            $this->assign('noControls', true);
        }
        else
        {
            $form = new ThemeEditForm($controls);

            $this->assign('inputArray', $form->getFormElements());

            $this->addForm($form);

            if ( PEEP::getRequest()->isPost() )
            {
                if ( $form->isValid($_POST) )
                {
                    $this->themeService->saveThemeControls($dto->getId(), $form->getValues());
                    $this->themeService->updateCustomCssFile($dto->getId());
                    $this->redirect();
                }
            }
        }

        $this->menu->getElement('settings')->setActive(true);
    }

    public function css()
    {
        if ( PEEP::getRequest()->isAjax() )
        {
            $css = isset($_POST['css']) ? trim($_POST['css']) : '';

            $dto = $this->themeService->findThemeByName(PEEP::getConfig()->getValue('base', 'selectedTheme'));
            $dto->setCustomCss($css);
            $this->themeService->saveTheme($dto);
            $this->themeService->updateCustomCssFile($dto->getId());

            echo json_encode(array('message' => PEEP::getLanguage()->text('admin', 'css_edit_success_message')));
        }

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'prettify.js');
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'lang-css.js');
        PEEP::getDocument()->addStyleSheet(PEEP::getPluginManager()->getPlugin('admin')->getStaticCssUrl() . 'prettify.css');
        PEEP::getDocument()->addOnloadScript("prettyPrint();");

        $fileString = file_get_contents(PEEP::getThemeManager()->getSelectedTheme()->getRootDir() . BOL_ThemeService::CSS_FILE_NAME);

        $this->assign('code', '<pre class="prettyprint lang-css">' . $fileString . '</pre>');

        $this->addForm(new AddCssForm());
    }

    public function graphics()
    {
        $images = $this->themeService->findAllCssImages();
        $assignArray = array();

        /* @var $value BOL_ThemeImage */
        foreach ( $images as $value )
        {
            $assignArray[] = array(
                'url' => PEEP::getStorage()->getFileUrl($this->themeService->getUserfileImagesDir() . $value->getFilename()),
                'delUrl' => PEEP::getRouter()->urlFor(__CLASS__, 'deleteImage', array('image-id' => $value->getId())),
                'cssUrl' => $this->themeService->getUserfileImagesUrl() . $value->getFilename()
            );
        }

        $this->assign('images', $assignArray);

        $form = new UploadGraphicsForm();
        $form->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $this->assign('confirmMessage', PEEP::getLanguage()->text('admin', 'theme_graphics_image_delete_confirm_message'));

        if ( PEEP::getRequest()->isPost() )
        {
            try
            {
                $this->themeService->addImage($_FILES['file']);
            }
            catch ( Exception $e )
            {
                PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_graphics_upload_form_fail_message'));
                $this->redirect();
            }

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'theme_graphics_upload_form_success_message'));
            $this->redirect();
        }
    }

    public function resetGraphics()
    {
        $this->themeService->resetImageControl(PEEP::getThemeManager()->getSelectedTheme()->getDto()->getId(), trim($_GET['name']));
        $this->redirectToAction('settings');
    }

    public function reset()
    {
        $dto = $this->themeService->findThemeByName(PEEP::getConfig()->getValue('base', 'selectedTheme'));
        $this->themeService->resetTheme($dto->getId());
        $this->redirectToAction('settings');
    }

    public function deleteImage( $params )
    {
        $this->themeService->deleteImage((int) $params['image-id']);
        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'theme_graphics_delete_success_message'));
        $this->redirectToAction('graphics');
    }
}

class UploadGraphicsForm extends Form
{

    public function __construct()
    {
        parent::__construct('upload_graphics');

        $this->addElement(new FileField('file'));

        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('admin', 'theme_graphics_upload_form_submit_label'));
        $this->addElement($submit);
    }
}

class AddCssForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-css');

        $text = new Textarea('css');
        $dto = BOL_ThemeService::getInstance()->findThemeByName(PEEP::getConfig()->getValue('base', 'selectedTheme'));
        $text->setValue($dto->getCustomCss());
        $this->addElement($text);

        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('admin', 'theme_css_edit_submit_label'));
        $this->addElement($submit);

        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->bindJsFunction(Form::BIND_SUCCESS, 'function(data){PEEP.info(data.message)}');
    }
}

class ThemeEditForm extends Form
{
    private $formElements = array();

    public function __construct( $controls )
    {
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        parent::__construct('theme-edit');

        $typeArray = array(
            'text' => 'TextField',
            'color' => 'ColorField',
            'font' => 'FontFamilyField',
            'image' => 'ImageField'        
        );

        $inputArray = array();
        
        foreach ( $controls as $value )
        {
            $refField = new ReflectionClass($typeArray[$value['type']]);
            $field = $refField->newInstance($value['key']);

            if ( $this->getElement($field->getName()) !== null )
            {
                continue;
            }

            $field->setValue($value['value'] !== null ? trim($value['value']) : trim($value['defaultValue']));
            $this->addElement($field);

            if ( !array_key_exists(trim($value['section']), $this->formElements) )
            {
                $this->formElements[trim($value['section'])] = array();
            }

            $this->formElements[trim($value['section'])][] = array('name' => $value['key'], 'title' => $value['label'], 'desc' => $value['description']);
        }

        ksort($this->formElements);

        $submit = new Submit('submit');
        $submit->setValue(PEEP::getLanguage()->text('admin', 'theme_settings_form_submit_label'));

        $this->addElement($submit);
    }

    public function getFormElements()
    {
        return $this->formElements;
    }
}

class FontFamilyField extends Selectbox
{

    public function __construct( $name )
    {
        parent::__construct($name);

        $this->setOptions(
            array(
                'default' => 'Default',
                'Arial, Helvetica, sans-serif' => 'Arial, Helvetica, sans-serif',
                'Times New Roman, Times, serif' => 'Times New Roman, Times, serif',
                'Courier New, Courier, monospace' => 'Courier New, Courier, monospace',
                'Georgia, Times New Roman, Times, serif' => 'Georgia, Times New Roman, Times, serif',
                'Verdana, Arial, Helvetica, sans-serif' => 'Verdana, Arial, Helvetica, sans-serif',
                'Geneva, Arial, Helvetica, sans-serif' => 'Geneva, Arial, Helvetica, sans-serif'
            )
        );

        $this->setHasInvitation(false);
    }
}

class ImageField extends FormElement
{
    private $mobile;

    public function __construct( $name, $mobile = false )
    {
        parent::__construct($name);
        $this->mobile = (bool)$mobile;
    }

    public function getValue()
    {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $output = '';

        if ( $this->value !== null && ( trim($this->value) !== 'none' ) )
        {
            if ( !strstr($this->value, 'http') )
            {
                $resultString = substr($this->value, (strpos($this->value, 'images/') + 7));
                $this->value = 'url(' . PEEP::getThemeManager()->getSelectedTheme()->getStaticImagesUrl($this->mobile) . substr($resultString, 0, strpos($resultString, ')')) . ')';
            }

            $randId = 'if' . rand(10, 10000000);

            $script = "$('#" . $randId . "').click(function(){
                new PEEP_FloatBox({\$title:'" . PEEP::getLanguage()->text('admin', 'themes_settings_graphics_preview_cap_label') . "', \$contents:$('#image_view_" . $this->getName() . "'), width:'550px'});
            });";

            PEEP::getDocument()->addOnloadScript($script);
            
            $output .= '<div class="clearfix"><a id="' . $randId . '" href="javascript://" class="theme_control theme_control_image" style="background-image:' . $this->value . ';"></a>
                <div style="float:left;padding:10px 0 0 10px;"><a href="javascript://" onclick="window.location=\'' . PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor('ADMIN_CTRL_Theme', 'resetGraphics'), array('name' => $this->getName())) . '\'">' . PEEP::getLanguage()->text('admin', 'themes_settings_reset_label') . '</a></div></div>
                <div style="display:none;"><div class="preview_graphics" id="image_view_' . $this->getName() . '" style="background-image:' . $this->value . '"></div></div>';
        }

        $output .= '<input type="file" name="' . $this->getName() . '" />';

        return $output;
    }
}