<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Themes extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_ThemeService
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
        $this->setDefaultAction('chooseTheme');
    }

    public function init()
    {
        $router = PEEP_Router::getInstance();

        $pageActions = array('choose_theme', 'add_theme');

        $menuItems = array();

        foreach ( $pageActions as $key => $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($item)->setLabel(PEEP::getLanguage()->text('admin', 'themes_menu_item_' . $item))->setOrder($key)->setUrl($router->urlFor(__CLASS__, $item));
            $menuItems[] = $menuItem;
        }

        $this->menu = new BASE_CMP_ContentMenu($menuItems);

        $this->addComponent('contentMenu', $this->menu);

        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'themes_choose_page_title'));
    }

    public function chooseTheme()
    {
        $language = PEEP::getLanguage();

        $this->themeService->updateThemeList();
        $this->themeService->updateThemesInfo();
        $themes = $this->themeService->findAllThemes();
        $themesInfo = array();

        $activeTheme = PEEP::getThemeManager()->getSelectedTheme()->getDto()->getName();

        /* @var $theme BOL_Theme */
        foreach ( $themes as $theme )
        {
            $themesInfo[$theme->getName()] = (array) json_decode($theme->getDescription());
            $themesInfo[$theme->getName()]['key'] = $theme->getName();
            $themesInfo[$theme->getName()]['title'] = $theme->getTitle();
            $themesInfo[$theme->getName()]['iconUrl'] = $this->themeService->getStaticUrl($theme->getName()) . BOL_ThemeService::ICON_FILE;
            $themesInfo[$theme->getName()]['previewUrl'] = $this->themeService->getStaticUrl($theme->getName()) . BOL_ThemeService::PREVIEW_FILE;
            $themesInfo[$theme->getName()]['active'] = ( $theme->getName() === $activeTheme );
            $themesInfo[$theme->getName()]['changeUrl'] = PEEP::getRouter()->urlFor(__CLASS__, 'changeTheme', array('theme' => $theme->getName()));
            $themesInfo[$theme->getName()]['update_url'] = ( ((int) $theme->getUpdate() === 1) && !defined('PEEP_PLUGIN_XP') ) ? PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'updateRequest', array('name' => $theme->getName())) : false;

            if ( !in_array($theme->getName(), array(BOL_ThemeService::DEFAULT_THEME, $activeTheme)) )
            {
                $themesInfo[$theme->getName()]['delete_url'] = PEEP::getRouter()->urlFor(__CLASS__, 'deleteTheme', array('name' => $theme->getName()));
            }
        }

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'theme_select.js');
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.sticky.js');

        $addData = array(
            'deleteConfirmMsg' => $language->text('admin', 'themes_choose_delete_confirm_msg'),
            'deleteActiveThemeMsg' => $language->text('admin', 'themes_cant_delete_active_theme')
        );

        PEEP::getDocument()->addOnloadScript(
            "window.peepThemes = new ThemesSelect(" . json_encode($themesInfo) . ", " . json_encode($addData) . ");
        	$('.selected_theme_info input.theme_select_submit').click(function(){
    			window.location.href = '" . $themesInfo[$activeTheme]['changeUrl'] . "';
    		});
            $('.selected_theme_info_stick').sticky({topSpacing:60});
            $('.admin_themes_select a.theme_icon').click( function(){ $('.theme_info .theme_control_button').hide(); });"
        );

        $adminTheme = PEEP::getThemeManager()->getThemeService()->getThemeObjectByName(BOL_ThemeService::DEFAULT_THEME);
        $defaultThemeImgUrl = $adminTheme === null ? "" : $adminTheme->getStaticImagesUrl();


        $this->assign("adminThemes", array(BOL_ThemeService::DEFAULT_THEME => $themesInfo[BOL_ThemeService::DEFAULT_THEME]));
        $this->assign('themeInfo', $themesInfo[$activeTheme]);
        $event = new PEEP_Event("admin.filter_themes_to_choose", array(), $themesInfo);
        PEEP::getEventManager()->trigger($event);
        $this->assign('themes', $event->getData());
        $this->assign('defaultThemeImgDir', $defaultThemeImgUrl);
    }

    public function addTheme()
    {
        $this->checkXP();

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_themes_add');
        $this->setPageHeading(PEEP::getLanguage()->text('admin', 'themes_add_theme_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_monitor');

        $language = PEEP::getLanguage();

        $form = new Form('theme-add');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $file = new FileField('file');
        $form->addElement($file);

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'plugins_manage_add_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $uploadMaxFilesize = (float) ini_get("upload_max_filesize");
                $postMaxSize = (float) ini_get("post_max_size");

                $serverLimit = $uploadMaxFilesize < $postMaxSize ? $uploadMaxFilesize : $postMaxSize;

                if ( ($_FILES['file']['error'] != UPLOAD_ERR_OK && $_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE ) || ( empty($_FILES['file']) || $_FILES['file']['size'] > $serverLimit * 1024 * 1024 ) )
                {
                    PEEP::getFeedback()->error($language->text('admin', 'manage_plugins_add_size_error_message', array('limit' => $serverLimit)));
                    $this->redirect();
                }

                if ( $_FILES['file']['error'] != UPLOAD_ERR_OK )
                {
                    switch ( $_FILES['file']['error'] )
                    {
                        case UPLOAD_ERR_INI_SIZE:
                            $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                            break;

                        case UPLOAD_ERR_PARTIAL:
                            $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                            break;

                        case UPLOAD_ERR_NO_FILE:
                            $error = $language->text('base', 'upload_file_no_file_error');
                            break;

                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                            break;

                        case UPLOAD_ERR_CANT_WRITE:
                            $error = $language->text('base', 'upload_file_cant_write_file_error');
                            break;

                        case UPLOAD_ERR_EXTENSION:
                            $error = $language->text('base', 'upload_file_invalid_extention_error');
                            break;

                        default:
                            $error = $language->text('base', 'upload_file_fail');
                    }

                    PEEP::getFeedback()->error($error);
                    $this->redirect();
                }

                if ( !is_uploaded_file($_FILES['file']['tmp_name']) )
                {
                    PEEP::getFeedback()->error($language->text('admin', 'manage_themes_add_empty_field_error_message'));
                    $this->redirect();
                }

                $tempFile = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('theme_add') . '.zip';
                $tempDir = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('theme_add') . DS;

                copy($_FILES['file']['tmp_name'], $tempFile);

                $zip = new ZipArchive();

                if ( $zip->open($tempFile) === true )
                {
                    $zip->extractTo($tempDir);
                    $zip->close();
                }
                else
                {
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_theme_add_extract_error'));
                    $this->redirectToAction();
                }

                unlink($tempFile);
                $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor(__CLASS__, 'processAdd'), array('dir' => urlencode($tempDir))));
            }
        }
    }

    public function processAdd()
    {
        $this->checkXP();
        $language = PEEP::getLanguage();

        if ( empty($_GET['dir']) || !file_exists(urldecode($_GET['dir'])) )
        {
            PEEP::getFeedback()->error($language->text('admin', 'manage_plugins_add_ftp_move_error'));
            $this->redirectToAction('add');
        }

        $tempDir = urldecode($_GET['dir']);
        $handle = opendir($tempDir);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $innerDir = $item;
            }

            closedir($handle);
        }

        if ( !empty($innerDir) && file_exists($tempDir . $innerDir . DS . 'theme.xml') )
        {
            $localDir = $tempDir . $innerDir . DS;
        }
        else
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_add_extract_error'));
            $this->redirectToAction('addTheme');
        }

        if ( file_exists(PEEP_DIR_THEME . $innerDir) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_add_duplicated_dir_error', array('dir' => $innerDir)));
            $this->redirectToAction('addTheme');
        }

        $ftp = $this->getFtpConnection();
        $ftp->uploadDir($localDir, PEEP_DIR_THEME . $innerDir);
        UTIL_File::removeDir($tempDir);
        PEEP::getFeedback()->info($language->text('base', 'themes_item_add_success_message'));
        $this->redirectToAction('chooseTheme');
    }

    public function changeTheme( $params )
    {
        PEEP::getConfig()->saveConfig('base', 'selectedTheme', trim($params['theme']));
        PEEP::getEventManager()->trigger(new PEEP_Event('base.change_theme', array('name' => $params['theme'])));
        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'theme_change_success_message'));
        $this->redirect(PEEP::getRouter()->uriForRoute('admin_themes_choose'));
    }

    private function checkXP()
    {
        if ( defined('PEEP_PLUGIN_XP') )
        {
            throw new Redirect404Exception();
        }
    }

    /**
     * Returns ftp connection.
     *
     * @return UTIL_Ftp
     */
    private function getFtpConnection()
    {
        try
        {
            $ftp = BOL_PluginService::getInstance()->getFtpConnection();
        }
        catch ( LogicException $e )
        {
            PEEP::getFeedback()->error($e->getMessage());
            $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'ftpAttrs'), array('back_uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
        }

        return $ftp;
    }
    /*     * **** Theme Update ******** */

    public function updateRequest( array $params )
    {
        $this->checkXP();
        $themeDto = $this->getThemeDtoByName($params);
        $language = PEEP::getLanguage();

        $remoteThemeInfo = (array) $this->themeService->getThemeInfoForUpdate($themeDto->getName(), $themeDto->getDeveloperKey());

        if ( empty($remoteThemeInfo) || !empty($remoteThemeInfo['error']) )
        {
            $this->assign('mode', 'error');
            $this->assign('text', $language->text('admin', 'theme_update_request_error'));
            $this->assign('returnUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'chooseTheme'));
        }
        else if ( (bool) $remoteThemeInfo['freeware'] )
        {
            $this->assign('mode', 'free');
            $this->assign('text', $language->text('admin', 'free_theme_request_text', array('oldVersion' => $themeDto->getBuild(), 'newVersion' => $remoteThemeInfo['build'], 'name' => $themeDto->getTitle())));
            $this->assign('redirectUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'update', $params));
            $this->assign('returnUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'chooseTheme'));
        }
        else if ( $remoteThemeInfo['build'] === null )
        {
            $query = "UPDATE `" . PEEP_DB_PREFIX . "base_theme` SET `update` = 0 WHERE `name` = :name";
            PEEP::getDbo()->query($query, array('name' => $params['name']));

            $this->assign('mode', 'error');
            $this->assign('text', $language->text('admin', 'theme_update_not_available_error'));
            $this->assign('returnUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'chooseTheme'));
        }
        else
        {
            $this->assign('text', $language->text('admin', 'com_theme_request_text', array('oldVersion' => $themeDto->getBuild(), 'newVersion' => $remoteThemeInfo['build'], 'name' => $themeDto->getTitle())));

            $form = new Form('license-key');

            $licenseKey = new TextField('key');
            $licenseKey->setValue($themeDto->getLicenseKey());
            $licenseKey->setRequired();
            $licenseKey->setLabel($language->text('admin', 'com_theme_request_name_label'));
            $form->addElement($licenseKey);

            $submit = new Submit('submit');
            $submit->setValue($language->text('admin', 'license_form_submit_label'));
            $form->addElement($submit);

            $button = new Button('button');
            $button->setValue($language->text('admin', 'license_form_leave_label'));
            $button->addAttribute('onclick', "window.location='" . PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'chooseTheme') . "'");
            $form->addElement($button);

            $this->addForm($form);

            if ( PEEP::getRequest()->isPost() )
            {
                if ( $form->isValid($_POST) )
                {
                    $data = $form->getValues();
                    $params['licenseKey'] = $data['key'];

                    $result = $this->themeService->checkLicenseKey($themeDto->getName(), $themeDto->getDeveloperKey(), $data['key']);

                    if ( $result === true )
                    {
                        $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Themes', 'update', $params));
                    }
                    else
                    {
                        PEEP::getFeedback()->error($language->text('admin', 'themes_manage_invalid_license_key_error_message'));
                        $this->redirect();
                    }
                }
            }
        }
    }

    public function update( array $params )
    {
        $this->checkXP();

        if ( !empty($_GET['mode']) )
        {
            switch ( trim($_GET['mode']) )
            {
                case 'theme_up_to_date':
                    PEEP::getFeedback()->warning(PEEP::getLanguage()->text('admin', 'manage_themes_up_to_date_message'));
                    break;

                case 'theme_update_success':
                    PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_themes_update_success_message'));
                    break;

                default :
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_themes_update_process_error'));
                    break;
            }

            $this->redirectToAction('chooseTheme');
        }

        $themeDto = $this->getThemeDtoByName($params);

        $ftp = $this->getFtpConnection();

        try
        {
            $archivePath = $this->themeService->downloadTheme($themeDto->getName(), $themeDto->getDeveloperKey(), (!empty($params['licenseKey']) ? $params['licenseKey'] : null));
        }
        catch ( Exception $e )
        {
            PEEP::getFeedback()->error($e->getMessage());
            $this->redirectToAction('chooseTheme');
        }

        if ( !file_exists($archivePath) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_update_download_error'));
            $this->redirectToAction('chooseTheme');
        }

        $zip = new ZipArchive();

        $tempDir = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('theme_update') . DS;

        if ( $zip->open($archivePath) === true )
        {
            $zip->extractTo($tempDir);
            $zip->close();
        }
        else
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_update_download_error'));
            $this->redirectToAction('chooseTheme');
        }

        if ( file_exists($tempDir . 'theme.xml') )
        {
            $localDir = $tempDir;
        }
        else
        {
            $handle = opendir($tempDir);

            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $innerDir = $item;
            }

            closedir($handle);

            if ( !empty($innerDir) && file_exists($tempDir . $innerDir . DS . 'theme.xml') )
            {
                $localDir = $tempDir . $innerDir;
            }
            else
            {
                PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'theme_update_download_error'));
                $this->redirectToAction('chooseTheme');
            }
        }


        if ( substr($name, -1) === DS )
        {
            $name = substr($name, 0, (strlen($name) - 1));
        }

        $remoteDir = PEEP_DIR_THEME . $themeDto->getName();

        if ( !file_exists($remoteDir) )
        {
            $ftp->mkDir($remoteDir);
        }

        $ftp->uploadDir($localDir, $remoteDir);
        UTIL_File::removeDir($localDir);

        $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP_URL_HOME . 'upgrade/index.php', array('theme' => $themeDto->getName(), 'back-uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
    }

    public function deleteTheme( $params )
    {
        $language = PEEP::getLanguage();
        $themeDto = $this->getThemeDtoByName($params);

        if ( PEEP::getThemeManager()->getDefaultTheme()->getDto()->getName() == $themeDto->getName() )
        {
            PEEP::getFeedback()->error($language->text('admin', 'themes_cant_delete_default_theme'));
            $this->redirectToAction('chooseTheme');
        }

        if ( PEEP::getThemeManager()->getCurrentTheme()->getDto()->getName() == $themeDto->getName() )
        {
            PEEP::getFeedback()->error($language->text('admin', 'themes_cant_delete_active_theme'));
            $this->redirectToAction('chooseTheme');
        }

        $ftp = $this->getFtpConnection();
        $this->themeService->deleteTheme($themeDto->getId(), true);
        $ftp->rmDir($this->themeService->getRootDir($themeDto->getName()));

        PEEP::getFeedback()->info($language->text('admin', 'themes_delete_success_message'));
        $this->redirectToAction('chooseTheme');
    }

    private function getThemeDtoByName( $params )
    {
        if ( !empty($params['name']) )
        {
            $themeDto = $this->themeService->findThemeByName(trim($params['name']));
        }

        if ( !empty($themeDto) )
        {
            return $themeDto;
        }

        PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_themes_theme_not_found'));
        $this->redirectToAction('chooseTheme');
    }
}
