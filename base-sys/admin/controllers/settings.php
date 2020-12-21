<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Settings extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    private function getMenu()
    {
        $language = PEEP::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('admin', 'menu_item_basics'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('admin_settings_main'));
        $item->setKey('basics');
        $item->setIconClass('peep_ic_gear_wheel');
        $item->setOrder(0);
        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('admin', 'menu_item_page_settings'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('admin_settings_page'));
        $item->setKey('page');
        $item->setIconClass('peep_ic_file');
        $item->setOrder(1);
        $menuItems[] = $item;

        if ( !defined('PEEP_PLUGIN_XP') )
        {
            $item = new BASE_MenuItem();
            $item->setLabel($language->text('admin', 'menu_item_mail_settings'));
            $item->setUrl(PEEP::getRouter()->urlForRoute('admin_settings_mail'));
            $item->setKey('mail');
            $item->setIconClass('peep_ic_mail');
            $item->setOrder(2);
            $menuItems[] = $item;
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    public function index()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_main_settings');
        }

        $language = PEEP::getLanguage();

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        $configSaveForm = new ConfigSaveForm();
        $this->addForm($configSaveForm);


        $configs = PEEP::getConfig()->getValues('base');

        if ( PEEP::getRequest()->isPost() && $configSaveForm->isValid($_POST) && isset($_POST['save']) )
        {
            $res = $configSaveForm->process();
            PEEP::getFeedback()->info($language->text('admin', 'main_settings_updated'));

            $this->redirect();
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_main_settings'));
            PEEP::getDocument()->setHeadingIconClass('peep_ic_gear_wheel');
        }

        $configSaveForm->getElement('siteTitle')->setValue($configs['site_name']);

        $this->assign('showVerifyButton', false);

        if ( defined('PEEP_PLUGIN_XP') )
        {
            $this->assign('showVerifyButton', $configs['unverify_site_email'] !== $configs['site_email']);
            $configSaveForm->getElement('siteEmail')->setValue($configs['unverify_site_email']);
        }
        else
        {
            $configSaveForm->getElement('siteEmail')->setValue($configs['site_email']);
        }

        $configSaveForm->getElement('tagline')->setValue($configs['site_tagline']);
        $configSaveForm->getElement('description')->setValue($configs['site_description']);
        $configSaveForm->getElement('timezone')->setValue($configs['site_timezone']);
        $configSaveForm->getElement('relativeTime')->setValue($configs['site_use_relative_time'] === '1' ? true : false);
        $configSaveForm->getElement('militaryTime')->setValue($configs['military_time'] === '1' ? true : false);
        $configSaveForm->getElement('currency')->setValue($configs['billing_currency']);

        $language->addKeyForJs('admin', 'verify_site_email');

        $jsDir = PEEP::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        PEEP::getDocument()->addScript($jsDir . "main_settings.js");

        $script = ' var main_settings = new mainSettings( ' . json_encode(PEEP::getRouter()->urlFor("ADMIN_CTRL_Settings", "ajaxResponder")) . ' )';

        PEEP::getDocument()->addOnloadScript($script);
    }

    private function getUsersMenu()
    {
        $language = PEEP::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('admin', 'menu_item_user_settings_general'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('admin_settings_user'));
        $item->setKey('general');
        $item->setIconClass('peep_ic_gear_wheel');
        $item->setOrder(0);
        $menuItems[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('admin', 'menu_item_user_settings_content_input'));
        $item->setUrl(PEEP::getRouter()->urlForRoute('admin_settings_user_input'));
        $item->setKey('content_input');
        $item->setIconClass('peep_ic_file');
        $item->setOrder(1);
        $menuItems[] = $item;

        return new BASE_CMP_ContentMenu($menuItems);
    }

    public function userInput()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_user_settings');
        }

        $language = PEEP::getLanguage();
        $config = PEEP::getConfig();

        $menu = $this->getUsersMenu();
        $menu->getElement('content_input')->setActive(true);
        $this->addComponent('menu', $menu);

        $settingsForm = new Form('input_settings');

        $userCustomHtml = new CheckboxField('user_custom_html');
        $userCustomHtml->setLabel($language->text('admin', 'input_settings_user_custom_html_disable_label'));
        $userCustomHtml->setDescription($language->text('admin', 'input_settings_user_custom_html_disable_desc'));
        $settingsForm->addElement($userCustomHtml);

        $userRichMedia = new CheckboxField('user_rich_media');
        $userRichMedia->setLabel($language->text('admin', 'input_settings_user_rich_media_disable_label'));
        $userRichMedia->setDescription($language->text('admin', 'input_settings_user_rich_media_disable_desc'));
        $settingsForm->addElement($userRichMedia);

        $commentsRichMedia = new CheckboxField('comments_rich_media');
        $commentsRichMedia->setLabel($language->text('admin', 'input_settings_comments_rich_media_disable_label'));
        $commentsRichMedia->setDescription($language->text('admin', 'input_settings_comments_rich_media_disable_desc'));
        $settingsForm->addElement($commentsRichMedia);

        $maxUploadMaxFilesize = BOL_FileService::getInstance()->getUploadMaxFilesize();

        $this->assign('maxUploadMaxFilesize', $maxUploadMaxFilesize);

        $maxUploadMaxFilesizeValidator = new FloatValidator(0, $maxUploadMaxFilesize);
        $maxUploadMaxFilesizeValidator->setErrorMessage($language->text('admin', 'settings_max_upload_size_error'));

        $maxUploadSize = new TextField('max_upload_size');
        $maxUploadSize->setLabel($language->text('admin', 'input_settings_max_upload_size_label'));
        $maxUploadSize->addValidator($maxUploadMaxFilesizeValidator);
        $settingsForm->addElement($maxUploadSize);

        $resourceList = new Textarea('resource_list');
        $resourceList->setLabel($language->text('admin', 'input_settings_resource_list_label'));
        $resourceList->setDescription($language->text('admin', 'input_settings_resource_list_desc'));
        $settingsForm->addElement($resourceList);
        
        $attchMaxUploadSize = new TextField('attch_max_upload_size');
        $attchMaxUploadSize->setLabel($language->text('admin', 'input_settings_attch_max_upload_size_label'));
        $attchMaxUploadSize->addValidator($maxUploadMaxFilesizeValidator);
        $settingsForm->addElement($attchMaxUploadSize);

        $attchExtList = new Textarea('attch_ext_list');
        $attchExtList->setLabel($language->text('admin', 'input_settings_attch_ext_list_label'));
        $attchExtList->setDescription($language->text('admin', 'input_settings_attch_ext_list_desc'));
        $settingsForm->addElement($attchExtList);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $settingsForm->addElement($submit);

        $this->addForm($settingsForm);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $settingsForm->isValid($_POST) )
            {
                $data = $settingsForm->getValues();

                $config->saveConfig('base', 'tf_comments_rich_media_disable', (int) $data['comments_rich_media']);
                $config->saveConfig('base', 'tf_user_custom_html_disable', (int) $data['user_custom_html']);
                $config->saveConfig('base', 'tf_user_rich_media_disable', (int) $data['user_rich_media']);
                $config->saveConfig('base', 'tf_max_pic_size', round((float) $data['max_upload_size'], 2));
                $config->saveConfig('base', 'attch_file_max_size_mb', round((float) $data['attch_max_upload_size'], 2));

                if ( !empty($data['resource_list']) )
                {
                    $res = array_unique(preg_split('/' . PHP_EOL . '/', $data['resource_list']));
                    $config->saveConfig('base', 'tf_resource_list', json_encode(array_map('trim', $res)));
                }

                $extList = array();

                if ( !empty($data['attch_ext_list']) )
                {
                    $extList = array_unique(preg_split('/' . PHP_EOL . '/', $data['attch_ext_list']));
                }

                $config->saveConfig('base', 'attch_ext_list', json_encode(array_map('trim', $extList)));

                PEEP::getFeedback()->info($language->text('admin', 'settings_submit_success_message'));
            }
            else
            {
                PEEP::getFeedback()->error('Error');
            }

            $this->redirect();
        }

        $userCustomHtml->setValue($config->getValue('base', 'tf_user_custom_html_disable'));
        $userRichMedia->setValue($config->getValue('base', 'tf_user_rich_media_disable'));
        $commentsRichMedia->setValue($config->getValue('base', 'tf_comments_rich_media_disable'));
        $maxUploadSize->setValue(round((float) $config->getValue('base', 'tf_max_pic_size'), 2));
        $resourceList->setValue(implode(PHP_EOL, json_decode($config->getValue('base', 'tf_resource_list'))));
        $attchMaxUploadSize->setValue(round((float) $config->getValue('base', 'attch_file_max_size_mb'), 2));
        $attchExtList->setValue(implode(PHP_EOL, json_decode($config->getValue('base', 'attch_ext_list'))));

        PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_user_input_settings'));
        PEEP::getDocument()->setHeadingIconClass('peep_ic_gear_wheel');
    }

    public function user()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_user_settings');
        }

        $language = PEEP::getLanguage();

        $menu = $this->getUsersMenu();
        $menu->getElement('general')->setActive(true);
        $this->addComponent('menu', $menu);

       
        $uploadMaxFilesize = (float) ini_get("upload_max_filesize");
        $postMaxSize = (float) ini_get("post_max_size");

        $maxUploadMaxFilesize = BOL_FileService::getInstance()->getUploadMaxFilesize();
        $this->assign('maxUploadMaxFilesize', $maxUploadMaxFilesize);       
        
        $userSettingsForm = new UserSettingsForm($maxUploadMaxFilesize);
        $this->addForm($userSettingsForm);

        $conf = PEEP::getConfig();
        
        

        
        $userSettingsForm->getElement('displayName')->setValue($conf->getValue('base', 'display_name_question'));

        $this->assign('displayConfirmEmail', !defined('PEEP_PLUGIN_XP'));

        if ( PEEP::getRequest()->isPost() && $userSettingsForm->isValid($_POST) )
        {
           

            $res = $userSettingsForm->process();
            PEEP::getFeedback()->info($language->text('admin', 'user_settings_updated'));
            $this->redirect();
        }

       

        

        

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_user_settings'));
            PEEP::getDocument()->setHeadingIconClass('peep_ic_gear_wheel');
        }

        PEEP::getNavigation()->deactivateMenuItems(PEEP_Navigation::ADMIN_SETTINGS);
    }

    public function page()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_main_settings');
        }

        $language = PEEP::getLanguage();
        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_page_settings'));
            PEEP::getDocument()->setHeadingIconClass('peep_ic_file');
        }

        $form = new Form('page_settings');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $headCode = new Textarea('head_code');
        $headCode->setLabel($language->text('admin', 'page_settings_form_headcode_label'));
        $headCode->setDescription($language->text('admin', 'page_settings_form_headcode_desc'));
        $form->addElement($headCode);

        $bottomCode = new Textarea('bottom_code');
        $bottomCode->setLabel($language->text('admin', 'page_settings_form_bottomcode_label'));
        $bottomCode->setDescription($language->text('admin', 'page_settings_form_bottomcode_desc'));
        $form->addElement($bottomCode);

        $favicon = new FileField('favicon');
        $favicon->setLabel($language->text('admin', 'page_settings_form_favicon_label'));
        $favicon->setDescription($language->text('admin', 'page_settings_form_favicon_desc'));
        $form->addElement($favicon);

        $enableFavicon = new CheckboxField('enable_favicon');
        $form->addElement($enableFavicon);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $form->addElement($submit);

        $faviconPath = PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'favicon.ico';
        $faviconUrl = PEEP::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico';

        $this->assign('faviconSrc', $faviconUrl);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                PEEP::getConfig()->saveConfig('base', 'html_head_code', $data['head_code']);
                PEEP::getConfig()->saveConfig('base', 'html_prebody_code', $data['bottom_code']);

                if ( !empty($_FILES['favicon']['name']) )
                {
                    if ( (int) $_FILES['favicon']['error'] === 0 && is_uploaded_file($_FILES['favicon']['tmp_name']) && UTIL_File::getExtension($_FILES['favicon']['name']) === 'ico' )
                    {
                        if ( file_exists($faviconPath) )
                        {
                            @unlink($faviconPath);
                        }

                        @move_uploaded_file($_FILES['favicon']['tmp_name'], $faviconPath);

                        if ( file_exists($_FILES['favicon']['tmp_name']) )
                        {
                            @unlink($_FILES['favicon']['tmp_name']);
                        }
                    }
                    else
                    {
                        PEEP::getFeedback()->error($language->text('admin', 'page_settings_favicon_submit_error_message'));
                    }
                }

                PEEP::getConfig()->saveConfig('base', 'favicon', !empty($data['enable_favicon']));
                PEEP::getFeedback()->info($language->text('admin', 'settings_submit_success_message'));
            }
            else
            {
                PEEP::getFeedback()->error($language->text('admin', 'settings_submit_error_message'));
            }

            $this->redirect();
        }

        $headCode->setValue(PEEP::getConfig()->getValue('base', 'html_head_code'));
        $bottomCode->setValue(PEEP::getConfig()->getValue('base', 'html_prebody_code'));
        $enableFavicon->setValue((int) PEEP::getConfig()->getValue('base', 'favicon'));
        $this->assign('faviconEnabled', PEEP::getConfig()->getValue('base', 'favicon'));

        $script = "$('#{$enableFavicon->getId()}').change(function(){ if(this.checked){ $('#favicon_enabled').show();$('#favicon_desabled').hide(); $('{$favicon->getId()}').attr('disabled', true);}else{ $('#favicon_enabled').hide();$('#favicon_desabled').show(); $('{$favicon->getId()}').attr('disabled', false);} });";
        PEEP::getDocument()->addOnloadScript($script);
    }

    public function mail()
    {
        if ( defined('PEEP_PLUGIN_XP') )
        {
            throw new Redirect404Exception();
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_main_settings');
        }

        $language = PEEP::getLanguage();

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        $mailSettingsForm = new MailSettingsForm();
        $this->addForm($mailSettingsForm);

        $configs = PEEP::getConfig()->getValues('base');

        //Mail settings
        $mailSettingsForm->getElement('mailSmtpEnabled')->setValue((bool) $configs['mail_smtp_enabled']);

        $mailSettingsForm->getElement('mailSmtpHost')->setValue($configs['mail_smtp_host'])->setRequired(true);
        $mailSettingsForm->getElement('mailSmtpUser')->setValue($configs['mail_smtp_user']);
        $mailSettingsForm->getElement('mailSmtpPassword')->setValue($configs['mail_smtp_password']);
        $mailSettingsForm->getElement('mailSmtpPort')->setValue($configs['mail_smtp_port']);
        $mailSettingsForm->getElement('mailSmtpConnectionPrefix')->setValue($configs['mail_smtp_connection_prefix']);

        if ( PEEP::getRequest()->isPost() && $mailSettingsForm->isValid($_POST) )
        {
            $res = $mailSettingsForm->process();
            PEEP::getFeedback()->info($language->text('admin', 'mail_settings_updated'));
            $this->redirect();
        }

        if ( !PEEP::getRequest()->isAjax() )
        {
            PEEP::getDocument()->setHeading(PEEP::getLanguage()->text('admin', 'heading_mail_settings'));
            PEEP::getDocument()->setHeadingIconClass('peep_ic_mail');

            PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_SETTINGS, 'admin', 'sidebar_menu_item_main_settings');
        }

        $smtpEnabled = false;
        if ( BOL_MailService::getInstance()->getTransfer() === BOL_MailService::TRANSFER_SMTP )
        {
            $smtpTestresponder = json_encode(PEEP::getRouter()->urlFor('ADMIN_CTRL_Settings', 'ajaxSmtpTestConnection'));
            $readyJs = "
                jQuery('#smtp_test_connection').click(function(){
                    window.PEEP.inProgressNode(this);
                    var self = this;
                    jQuery.get($smtpTestresponder, function(r){
                        window.PEEP.activateNode(self);
                        alert(r);
                    });
                });
            ";
            PEEP::getDocument()->addOnloadScript($readyJs);
            $smtpEnabled = true;
        }

        $this->assign('smtpEnabled', $smtpEnabled);
    }

    public function ajaxSmtpTestConnection()
    {
        if ( !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        try
        {
            $result = BOL_MailService::getInstance()->smtpTestConnection();
        }
        catch ( LogicException $e )
        {
            exit($e->getMessage());
        }

        if ( $result )
        {
            $responce = PEEP::getLanguage()->text('admin', 'smtp_test_connection_success');
        }
        else
        {
            $responce = PEEP::getLanguage()->text('admin', 'smtp_test_connection_failed');
        }

        exit($responce);
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'sendVerifyEmail':

                $result = false;

                $email = trim($_POST["email"]);

                if ( UTIL_Validator::isEmailValid($email) )
                {
                    PEEP::getConfig()->saveConfig('base', 'unverify_site_email', $email);

                    $siteEmail = PEEP::getConfig()->getValue('base', 'site_email');

                    if ( $siteEmail !== $email )
                    {
                        $type = 'info';
                        BOL_EmailVerifyService::getInstance()->sendSiteVerificationMail(false);
                        $message = PEEP::getLanguage()->text('base', 'email_verify_verify_mail_was_sent');
                        $result = true;
                    }
                    else
                    {
                        $type = 'warning';
                        $message = PEEP::getLanguage()->text('admin', 'email_already_verify');
                    }
                }

                $responce = json_encode(array('result' => $result, 'type' => $type, 'message' => $message));

                break;
        }

        exit($responce);
    }
}

/**
 * Save Configurations form class
 */
class ConfigSaveForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct('configSaveForm');

        $language = PEEP::getLanguage();

        $siteTitleField = new TextField('siteTitle');
        $siteTitleField->setRequired(true);
        $this->addElement($siteTitleField);

        $siteEmailField = new TextField('siteEmail');
        $siteEmailField->setRequired(true);
        $siteEmailField->addValidator(new EmailValidator());
        $this->addElement($siteEmailField);

        $taglineField = new TextField('tagline');
        $taglineField->setRequired(true);
        $this->addElement($taglineField);

        $descriptionField = new Textarea('description');
        $descriptionField->setRequired(true);
        $this->addElement($descriptionField);

        $timezoneField = new Selectbox('timezone');
        $timezoneField->setRequired(true);
        $timezoneField->setOptions($this->getTimezones());
        $this->addElement($timezoneField);

        $relativeTimeField = new CheckboxField('relativeTime');
        $this->addElement($relativeTimeField);

        $militaryTimeField = new CheckboxField('militaryTime');
        $this->addElement($militaryTimeField);

        // -- date format --
        $dateFieldFormat = new Selectbox("dateFieldFormat");
        $dateFieldFormat->setLabel($language->text('base', 'questions_config_date_field_format_label'));

        $dateFormatValue = PEEP::getConfig()->getValue('base', 'date_field_format');

        $dateFormatArray = array(BOL_QuestionService::DATE_FIELD_FORMAT_MONTH_DAY_YEAR, BOL_QuestionService::DATE_FIELD_FORMAT_DAY_MONTH_YEAR);

        $options = array();

        foreach ( $dateFormatArray as $key )
        {
            $options[$key] = $language->text('base', 'questions_config_date_field_format_' . $key);
        }

        $dateFieldFormat->setOptions($options);
        $dateFieldFormat->setHasInvitation(false);
        $dateFieldFormat->setValue($dateFormatValue);
        $dateFieldFormat->setRequired();

        $this->addElement($dateFieldFormat);
        // -- date format --

        $currencyField = new Selectbox('currency');
        $currList = BOL_BillingService::getInstance()->getCurrencies();
        foreach ( $currList as $key => $cur )
        {
            $currList[$key] = $key . ' (' . $cur . ')';
        }
        $currencyField->setOptions($currList);
        $currencyField->setLabel($language->text('admin', 'currency'));
        $currencyField->setRequired(true);
        $this->addElement($currencyField);

//        $imagesAllowPicUpload = new CheckboxField('tf-allow-pic-upload');
//
//        $imagesAllowPicUpload->setLabel(PEEP::getLanguage()->text('base', 'tf_allow_pics'))
//            ->setValue(PEEP::getConfig()->getValue('base', 'tf_allow_pic_upload'));
//
//        $this->addElement($imagesAllowPicUpload);
//
//        $imageMaxSizeField = new TextField('tf-max-image-size');
//
//        $imageMaxSizeField->setValue(PEEP::getConfig()->getValue('base', 'tf_max_pic_size'))
//            ->setLabel(PEEP::getLanguage()->text('base', 'tf_max_img_size'))
//            ->addValidator(new IntValidator())->setRequired();
//
//        $this->addElement($imageMaxSizeField);
        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $this->addElement($submit);
    }

    /**
     * Updates video plugin configuration
     *
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();
        $config = PEEP::getConfig();

        //begin update lang cache
        $siteName = $config->getValue('base', 'site_name');

        $config->saveConfig('base', 'site_name', $values['siteTitle']);

        if ( $siteName != $config->getValue('base', 'site_name') )
        {
            BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
        }

        if ( defined('PEEP_PLUGIN_XP') )
        {
            //end update lang cache
            $siteEmail = $config->getValue('base', 'unverify_site_email');

            if ( $siteEmail !== trim($values['siteEmail']) )
            {
                $config->saveConfig('base', 'unverify_site_email', $values['siteEmail']);
                BOL_EmailVerifyService::getInstance()->sendSiteVerificationMail();
            }
        }
        else
        {
            $config->saveConfig('base', 'site_email', $values['siteEmail']);
        }

//        join_display_photo_upload  	true  	Display photo upload on join page
//        join_photo_upload_set_required 	false 	Set required photo upload field on join page
//        join_display_terms_of_use

        $config->saveConfig('base', 'site_tagline', $values['tagline']);
        $config->saveConfig('base', 'site_description', $values['description']);
        $config->saveConfig('base', 'site_timezone', $values['timezone']);
        $config->saveConfig('base', 'site_use_relative_time', $values['relativeTime'] ? '1' : '0');
        $config->saveConfig('base', 'military_time', $values['militaryTime'] ? '1' : '0');
        $config->saveConfig('base', 'date_field_format', $values['dateFieldFormat']);
        $config->saveConfig('base', 'billing_currency', $values['currency']);
//        $config->saveConfig('base', 'tf_allow_pic_upload', $values['tf-allow-pic-upload']);
//        $config->saveConfig('base', 'tf_max_pic_size', $values['tf-max-image-size']);

        return array('result' => true);
    }

    private function getTimezones()
    {
        $zones = array(
            "Australia/West", "Australia/Victoria", "Australia/Tasmania", "Australia/Sydney", "Australia/South",
            "Australia/Queensland", "Australia/Perth", "Australia/North", "Australia/NSW", "Australia/Melbourne",
            "Australia/Lord_Howe", "Australia/Lindeman", "Australia/LHI", "Australia/Hobart", "Australia/Darwin",
            "Australia/Currie", "Australia/Canberra", "Australia/Broken_Hill", "Australia/Brisbane", "Australia/Adelaide",
            "Australia/ACT", "Atlantic/Stanley", "Atlantic/St_Helena", "Atlantic/South_Georgia", "Atlantic/Reykjavik",
            "Atlantic/Madeira", "Atlantic/Jan_Mayen", "Atlantic/Faeroe", "Atlantic/Cape_Verde", "Atlantic/Canary",
            "Atlantic/Bermuda", "Atlantic/Azores", "Asia/Yerevan", "Asia/Yekaterinburg", "Asia/Yakutsk",
            "Asia/Vladivostok", "Asia/Vientiane", "Asia/Urumqi", "Asia/Ulan_Bator", "Asia/Ulaanbaatar",
            "Asia/Ujung_Pandang", "Asia/Tokyo", "Asia/Thimphu", "Asia/Thimbu", "Asia/Tel_Aviv",
            "Asia/Tehran", "Asia/Tbilisi", "Asia/Tashkent", "Asia/Taipei", "Asia/Singapore",
            "Asia/Shanghai", "Asia/Seoul", "Asia/Samarkand", "Asia/Sakhalin", "Asia/Saigon",
            "Asia/Riyadh", "Asia/Rangoon", "Asia/Qyzylorda", "Asia/Qatar", "Asia/Pyongyang",
            "Asia/Pontianak", "Asia/Phnom_Penh", "Asia/Oral", "Asia/Omsk", "Asia/Novosibirsk",
            "Asia/Nicosia", "Asia/Muscat", "Asia/Manila", "Asia/Makassar", "Asia/Magadan",
            "Asia/Macau", "Asia/Macao", "Asia/Kuwait", "Asia/Kuching", "Asia/Kuala_Lumpur",
            "Asia/Krasnoyarsk", "Asia/Katmandu", "Asia/Kashgar", "Asia/Karachi", "Asia/Kamchatka",
            "Asia/Kabul", "Asia/Jerusalem", "Asia/Jayapura", "Asia/Jakarta", "Asia/Istanbul",
            "Asia/Irkutsk", "Asia/Hovd", "Asia/Hong_Kong", "Asia/Harbin", "Asia/Gaza",
            "Asia/Dushanbe", "Asia/Dubai", "Asia/Dili", "Asia/Dhaka", "Asia/Damascus",
            "Asia/Dacca", "Asia/Colombo", "Asia/Chungking", "Asia/Chongqing", "Asia/Choibalsan",
            "Asia/Calcutta", "Asia/Brunei", "Asia/Bishkek", "Asia/Beirut", "Asia/Bangkok",
            "Asia/Baku", "Asia/Bahrain", "Asia/Baghdad", "Asia/Ashkhabad", "Asia/Ashgabat",
            "Asia/Aqtobe", "Asia/Aqtau", "Asia/Anadyr", "Asia/Amman", "Asia/Almaty",
            "Asia/Aden", "Antarctica/Vostok", "Antarctica/Syowa", "Antarctica/South_Pole", "Antarctica/Rothera",
            "Antarctica/Palmer", "Antarctica/McMurdo", "Antarctica/Mawson", "Antarctica/DumontDUrville",
            "Antarctica/Davis", "Antarctica/Casey", "America/Yellowknife", "America/Yakutat", "America/Winnipeg",
            "America/Whitehorse", "America/Virgin", "America/Vancouver", "America/Tortola", "America/Toronto",
            "America/Tijuana", "America/Thunder_Bay", "America/Thule", "America/Tegucigalpa", "America/Swift_Current",
            "America/St_Vincent", "America/St_Thomas", "America/St_Lucia", "America/St_Kitts", "America/St_Johns",
            "America/Shiprock", "America/Scoresbysund", "America/Sao_Paulo", "America/Santo_Domingo", "America/Santiago",
            "America/Rosario", "America/Rio_Branco", "America/Regina", "America/Recife", "America/Rankin_Inlet",
            "America/Rainy_River", "America/Puerto_Rico", "America/Porto_Velho", "America/Porto_Acre", "America/Port_of_Spain",
            "America/Port-au-Prince", "America/Phoenix", "America/Paramaribo", "America/Pangnirtung", "America/Panama",
            "America/North_Dakota/Center", "America/Noronha", "America/Nome", "America/Nipigon", "America/New_York",
            "America/Nassau", "America/Montserrat", "America/Montreal", "America/Montevideo", "America/Monterrey",
            "America/Miquelon", "America/Mexico_City", "America/Merida", "America/Menominee", "America/Mendoza",
            "America/Mazatlan", "America/Martinique", "America/Manaus", "America/Managua", "America/Maceio",
            "America/Louisville", "America/Los_Angeles", "America/Lima", "America/La_Paz", "America/Knox_IN",
            "America/Kentucky/Monticello", "America/Kentucky/Louisville", "America/Juneau", "America/Jujuy", "America/Jamaica",
            "America/Iqaluit", "America/Inuvik", "America/Indianapolis", "America/Indiana/Vevay", "America/Indiana/Marengo",
            "America/Indiana/Knox", "America/Indiana/Indianapolis", "America/Hermosillo", "America/Havana", "America/Halifax",
            "America/Guyana", "America/Guayaquil", "America/Guatemala", "America/Guadeloupe", "America/Grenada",
            "America/Grand_Turk", "America/Goose_Bay", "America/Godthab", "America/Glace_Bay", "America/Fortaleza",
            "America/Fort_Wayne", "America/Ensenada", "America/El_Salvador", "America/Eirunepe", "America/Edmonton",
            "America/Dominica", "America/Detroit", "America/Denver", "America/Dawson_Creek", "America/Dawson",
            "America/Danmarkshavn", "America/Curacao", "America/Cuiaba", "America/Costa_Rica", "America/Cordoba",
            "America/Coral_Harbour", "America/Chihuahua", "America/Chicago", "America/Cayman", "America/Cayenne",
            "America/Catamarca", "America/Caracas", "America/Cancun", "America/Campo_Grande", "America/Cambridge_Bay",
            "America/Buenos_Aires", "America/Boise", "America/Bogota", "America/Boa_Vista", "America/Belize",
            "America/Belem", "America/Barbados", "America/Bahia", "America/Atka", "America/Asuncion",
            "America/Aruba", "America/Argentina/Ushuaia", "America/Argentina/Tucuman", "America/Argentina/San_Juan", "America/Argentina/Rio_Gallegos",
            "America/Argentina/Mendoza", "America/Argentina/La_Rioja", "America/Argentina/Jujuy", "America/Argentina/Cordoba", "America/Argentina/ComodRivadavia",
            "America/Argentina/Catamarca", "America/Argentina/Buenos_Aires", "America/Araguaina", "America/Antigua", "America/Anguilla",
            "America/Anchorage", "America/Adak", "Africa/Windhoek", "Africa/Tunis", "Africa/Tripoli",
            "Africa/Timbuktu", "Africa/Sao_Tome", "Africa/Porto-Novo", "Africa/Ouagadougou", "Africa/Nouakchott",
            "Africa/Niamey", "Africa/Ndjamena", "Africa/Nairobi", "Africa/Monrovia", "Africa/Mogadishu",
            "Africa/Mbabane", "Africa/Maseru", "Africa/Maputo", "Africa/Malabo", "Africa/Lusaka",
            "Africa/Lubumbashi", "Africa/Luanda", "Africa/Lome", "Africa/Libreville", "Africa/Lagos",
            "Africa/Kinshasa", "Africa/Kigali", "Africa/Khartoum", "Africa/Kampala", "Africa/Johannesburg",
            "Africa/Harare", "Africa/Gaborone", "Africa/Freetown", "Africa/El_Aaiun", "Africa/Douala",
            "Africa/Djibouti", "Africa/Dar_es_Salaam", "Africa/Dakar", "Africa/Conakry", "Africa/Ceuta",
            "Africa/Casablanca", "Africa/Cairo", "Africa/Bujumbura", "Africa/Brazzaville", "Africa/Blantyre",
            "Africa/Bissau", "Africa/Banjul", "Africa/Bangui", "Africa/Bamako", "Africa/Asmera",
            "Africa/Algiers", "Africa/Addis_Ababa", "Africa/Accra", "Africa/Abidjan", "Australia/Yancowinna",
            "Brazil/Acre", "Brazil/DeNoronha", "Brazil/East", "Brazil/West", "Canada/Atlantic",
            "Canada/Central", "Canada/East-Saskatchewan", "Canada/Eastern", "Canada/Mountain", "Canada/Newfoundland",
            "Canada/Pacific", "Canada/Saskatchewan", "Canada/Yukon", "Chile/Continental", "Chile/EasterIsland",
            "Europe/Amsterdam", "Europe/Andorra", "Europe/Athens", "Europe/Belfast", "Europe/Belgrade",
            "Europe/Berlin", "Europe/Bratislava", "Europe/Brussels", "Europe/Bucharest", "Europe/Budapest",
            "Europe/Chisinau", "Europe/Copenhagen", "Europe/Dublin", "Europe/Gibraltar", "Europe/Helsinki",
            "Europe/Istanbul", "Europe/Kaliningrad", "Europe/Kiev", "Europe/Lisbon", "Europe/Ljubljana",
            "Europe/London", "Europe/Luxembourg", "Europe/Madrid", "Europe/Malta", "Europe/Mariehamn",
            "Europe/Minsk", "Europe/Monaco", "Europe/Moscow", "Europe/Nicosia", "Europe/Oslo",
            "Europe/Paris", "Europe/Prague", "Europe/Riga", "Europe/Rome", "Europe/Samara",
            "Europe/San_Marino", "Europe/Sarajevo", "Europe/Simferopol", "Europe/Skopje", "Europe/Sofia",
            "Europe/Stockholm", "Europe/Tallinn", "Europe/Tirane", "Europe/Tiraspol", "Europe/Uzhgorod",
            "Europe/Vaduz", "Europe/Vatican", "Europe/Vienna", "Europe/Vilnius", "Europe/Warsaw",
            "Europe/Zagreb", "Europe/Zaporozhye", "Europe/Zurich", "Indian/Antananarivo", "Indian/Chagos",
            "Indian/Christmas", "Indian/Cocos", "Indian/Comoro", "Indian/Kerguelen", "Indian/Mahe",
            "Indian/Maldives", "Indian/Mauritius", "Indian/Mayotte", "Indian/Reunion", "Mexico/BajaNorte",
            "Mexico/BajaSur", "Mexico/General", "Pacific/Apia", "Pacific/Auckland", "Pacific/Chatham",
            "Pacific/Easter", "Pacific/Efate", "Pacific/Enderbury", "Pacific/Fakaofo", "Pacific/Fiji",
            "Pacific/Funafuti", "Pacific/Galapagos", "Pacific/Gambier", "Pacific/Guadalcanal", "Pacific/Guam",
            "Pacific/Honolulu", "Pacific/Johnston", "Pacific/Kiritimati", "Pacific/Kosrae", "Pacific/Kwajalein",
            "Pacific/Majuro", "Pacific/Marquesas", "Pacific/Midway", "Pacific/Nauru", "Pacific/Niue",
            "Pacific/Norfolk", "Pacific/Noumea", "Pacific/Pago_Pago", "Pacific/Palau", "Pacific/Pitcairn",
            "Pacific/Ponape", "Pacific/Port_Moresby", "Pacific/Rarotonga", "Pacific/Saipan", "Pacific/Samoa",
            "Pacific/Tahiti", "Pacific/Tarawa", "Pacific/Tongatapu", "Pacific/Truk", "Pacific/Wake",
            "Pacific/Wallis", "Pacific/Yap", "US/Alaska", "US/Aleutian", "US/Arizona",
            "US/Central", "US/East-Indiana", "US/Eastern", "US/Hawaii", "US/Indiana-Starke",
            "US/Michigan", "US/Mountain", "US/Pacific", "US/Pacific-New", "US/Samoa"
        );

        $timezones = array();
        foreach ( $zones as $z )
        {
            $timezones[$z] = $z;
        }

        return $timezones;
    }
}

/**
 * Save Configurations form class
 */
class UserSettingsForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct($maxUploadMaxFilesize)
    {
        parent::__construct('userSettingsForm');

        $this->setEnctype("multipart/form-data");

        $language = PEEP::getLanguage();

        
        
       
        
        if ( !defined('PEEP_PLUGIN_XP') )
        {
            // confirm Email
            $confirmEmail = new CheckboxField('confirmEmail');
            $confirmEmail->setValue(PEEP::getConfig()->getValue('base', 'confirm_email'));
            $this->addElement($confirmEmail->setLabel($language->text('admin', 'user_settings_confirm_email')));
        }

        // display name Field
        $displayNameField = new Selectbox('displayName');
        $displayNameField->setRequired(true);

        $questions = array(
            'username' => $language->text('base', 'questions_question_username_label'),
            'realname' => $language->text('base', 'questions_question_realname_label')
        );

        $displayNameField->setHasInvitation(false);
        $displayNameField->setOptions($questions);
        $this->addElement($displayNameField->setLabel($language->text('admin', 'user_settings_display_name')));

       

        // --

        $joinConfigField = new Selectbox('join_display_photo_upload');

        $options = array(
            BOL_UserService::CONFIG_JOIN_DISPLAY_PHOTO_UPLOAD => $language->text('base', 'config_join_display_photo_upload_display_label'),
            BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD => $language->text('base', 'config_join_display_photo_upload_display_and_require_label'),
            BOL_UserService::CONFIG_JOIN_NOT_DISPLAY_PHOTO_UPLOAD => $language->text('base', 'config_join_display_photo_upload_not_display_label')
        );

        $joinConfigField->addOptions($options);
        $joinConfigField->setHasInvitation(false);
        $joinConfigField->setValue(PEEP::getConfig()->getValue('base', 'join_display_photo_upload'));
        $this->addElement($joinConfigField);

        // --

        $joinConfigField = new CheckboxField('join_display_terms_of_use');
        $joinConfigField->setValue(PEEP::getConfig()->getValue('base', 'join_display_terms_of_use'));
        $this->addElement($joinConfigField);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $this->addElement($submit);
    }

    /**
     * Updates user settings configuration
     *
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();

        $config = PEEP::getConfig();

        
        $config->saveConfig('base', 'display_name_question', $values['displayName']);

        $config->saveConfig('base', 'join_display_photo_upload', $values['join_display_photo_upload']);
        $config->saveConfig('base', 'join_display_terms_of_use', $values['join_display_terms_of_use']);
        
       

        if ( !defined('PEEP_PLUGIN_XP') )
        {
            $config->saveConfig('base', 'confirm_email', $values['confirmEmail']);
        }

       

        
        return array('result' => true);
    }
}

/**
 * Save Configurations form class
 */
class MailSettingsForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct('mailSettingsForm');

        $language = PEEP::getLanguage();

        // Mail Settings
        $smtpField = new CheckboxField('mailSmtpEnabled');
        $this->addElement($smtpField);

        $smtpField = new TextField('mailSmtpHost');
        $this->addElement($smtpField);

        $smtpField = new TextField('mailSmtpUser');
        $this->addElement($smtpField);

        $smtpField = new TextField('mailSmtpPassword');
        $this->addElement($smtpField);

        $smtpField = new TextField('mailSmtpPort');
        $this->addElement($smtpField);

        $smtpField = new Selectbox('mailSmtpConnectionPrefix');
        $smtpField->setHasInvitation(true);
        $smtpField->setInvitation(PEEP::getLanguage()->text('admin', 'mail_smtp_secure_invitation'));
        $smtpField->addOption('ssl', 'SSL');
        $smtpField->addOption('tls', 'TLS');
        $this->addElement($smtpField);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $this->addElement($submit);
    }

    /**
     * Updates user settings configuration
     *
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();
        $config = PEEP::getConfig();

        $config->saveConfig('base', 'mail_smtp_enabled', $values['mailSmtpEnabled'] ? '1' : '0');
        $config->saveConfig('base', 'mail_smtp_host', $values['mailSmtpHost']);
        $config->saveConfig('base', 'mail_smtp_user', $values['mailSmtpUser']);
        $config->saveConfig('base', 'mail_smtp_password', $values['mailSmtpPassword']);
        $config->saveConfig('base', 'mail_smtp_port', $values['mailSmtpPort']);
        $config->saveConfig('base', 'mail_smtp_connection_prefix', $values['mailSmtpConnectionPrefix']);

        return array('result' => true);
    }
}