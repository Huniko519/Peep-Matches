<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CTRL_Plugins extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pluginService = BOL_PluginService::getInstance();
    }

    /**
     * Default action. Shows the list of all installed plugins.
     */
    public function index()
    {
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_plugins_installed');

        $language = PEEP::getLanguage();
        $this->setPageTitle($language->text('admin', 'page_title_manage_plugins'));
        $this->setPageHeading($language->text('admin', 'page_title_manage_plugins'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');

        $this->pluginService->updatePluginsXmlInfo();
        // get plugins in DB
        $plugins = $this->pluginService->findRegularPlugins();

        usort($plugins, array(__CLASS__, 'sortPlugins'));

        $arrayToAssign['active'] = array();
        $arrayToAssign['inactive'] = array();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            $array = array(
                'title' => $plugin->getTitle(),
                'description' => $plugin->getDescription(),
                'set_url' => ( $plugin->isActive && $plugin->getAdminSettingsRoute() !== null) ? PEEP::getRouter()->urlForRoute($plugin->adminSettingsRoute) : false,
                'update_url' => ( ((int) $plugin->getUpdate() === 1) && !defined('PEEP_PLUGIN_XP') ) ? PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'updateRequest', array('key' => $plugin->getKey())) : false
            );

            if ( $plugin->isActive() )
            {
                $array['deact_url'] = PEEP::getRouter()->urlFor(__CLASS__, 'deactivate', array('key' => $plugin->getKey()));
                $array['un_url'] = ( $plugin->getUninstallRoute() === null ? PEEP::getRouter()->urlFor(__CLASS__, 'uninstallRequest', array('key' => $plugin->getKey())) : PEEP::getRouter()->urlForRoute($plugin->getUninstallRoute()) );
                $arrayToAssign['active'][$plugin->getKey()] = $array;
            }
            else
            {
                $array['active_url'] = PEEP::getRouter()->urlFor(__CLASS__, 'activate', array('key' => $plugin->getKey()));
                $arrayToAssign['inactive'][$plugin->getKey()] = $array;
            }
        }

        $event = new PEEP_Event('admin.plugins_list_view', array('ctrl' => $this, 'type' => 'index'), $arrayToAssign);
        PEEP::getEventManager()->trigger($event);
        $arrayToAssign = $event->getData();

        $this->assign('plugins', $arrayToAssign);
    }

    /**
     * Action shows the list of plugins available for installation.
     */
    public function available()
    {
        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_plugins_available');

        // read plugins dir and find available plugins
        $arrayToAssign = $this->pluginService->getAvailablePluginsList();

        /* @var $plugin BOL_Plugin */
        foreach ( $arrayToAssign as $key => $plugin )
        {
            $arrayToAssign[$key]['inst_url'] = PEEP::getRouter()->urlFor(__CLASS__, 'install', array('key' => $plugin['key']));
            $arrayToAssign[$key]['del_url'] = PEEP::getRouter()->urlFor(__CLASS__, 'delete', array('key' => $plugin['key']));
        }

        $event = new PEEP_Event('admin.plugins_list_view', array('ctrl' => $this, 'type' => 'available'), $arrayToAssign);
        PEEP::getEventManager()->trigger($event);
        $arrayToAssign = $event->getData();
        $this->assign('plugins', $arrayToAssign);
    }

    public static function sortPlugins( BOL_Plugin $a, BOL_Plugin $b )
    {
        $aChar = substr($a->getTitle(), 0, 1);
        $bChar = substr($b->getTitle(), 0, 1);

        if ( $aChar == $bChar )
        {
            return 0;
        }

        return $aChar > $bChar;
    }

    /**
     * Upload and add new plugins.
     */
    public function add()
    {
        $this->checkXP();

        PEEP::getNavigation()->activateMenuItem(PEEP_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_plugins_add');

        $language = PEEP::getLanguage();

        $form = new Form('plugin-add');
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

                // check server file upload limits
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
                    PEEP::getFeedback()->error($language->text('admin', 'manage_plugins_add_empty_field_error_message'));
                    $this->redirect();
                }

                $tempFile = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('plugin_add') . '.zip';
                $tempDir = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('plugin_add') . DS;

                copy($_FILES['file']['tmp_name'], $tempFile);


                $zip = new ZipArchive();

                if ( $zip->open($tempFile) === true )
                {
                    $zip->extractTo($tempDir);
                    $zip->close();
                }
                else
                {
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugin_add_extract_error'));
                    $this->redirectToAction('index');
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

        if ( !empty($innerDir) && file_exists($tempDir . $innerDir . DS . 'plugin.xml') )
        {
            $localDir = $tempDir . $innerDir . DS;
        }
        else
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugin_add_extract_error'));
            $this->redirectToAction('index');
        }

        //get plugin.xml info
        $pluginXmlInfo = $this->pluginService->readPluginXmlInfo($tempDir . $innerDir . DS . 'plugin.xml');
        $plugin = $this->pluginService->findPluginByKey($pluginXmlInfo['key']);
        $pluginWithDevKey = $this->pluginService->findPluginByKey($pluginXmlInfo['key'], $pluginXmlInfo['developerKey']);

        if ( $plugin !== null )
        {
            if ( $pluginWithDevKey !== null )
            {
                $pluginDir = PEEP_DIR_PLUGIN . $plugin->getModule() . DS;
            }
            else
            {
                PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugin_cant_add_duplicate_key_error'));
                $this->redirectToAction('index');
            }
        }
        else
        {
            $pluginDir = false;
            $itemsXmlList = BOL_PluginService::getInstance()->getPluginsXmlInfo();

            foreach ( $itemsXmlList as $xmlItem )
            {
                if ( $xmlItem["key"] == $pluginXmlInfo["key"] && $xmlItem["developerKey"] == $pluginXmlInfo['developerKey'] )
                {
                    $pluginDir = $xmlItem["path"];
                }
            }

            if ( !$pluginDir )
            {
                $pluginDir = PEEP_DIR_PLUGIN . $innerDir;

                while ( file_exists($pluginDir) )
                {
                    $pluginDir .= rand(1, 99);
                }
            }
        }

        $ftp = $this->getFtpConnection();
        $ftp->uploadDir($localDir, $pluginDir);
        UTIL_File::removeDir($tempDir);
        PEEP::getFeedback()->info($language->text('base', 'manage_plugins_add_success_message'));
        $this->redirectToAction('available');
    }

    /**
     * Deactivates plugin.
     *
     * @param array $params
     */
    public function deactivate( array $params )
    {
        $pluginDto = $this->getPluginDtoByKey($params);
        $language = PEEP::getLanguage();
        // trigger event
        $event = new PEEP_Event(PEEP_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array('pluginKey' => $pluginDto->getKey()));
        PEEP::getEventManager()->trigger($event);
        $this->pluginService->deactivate($pluginDto->getKey());
        PEEP::getFeedback()->info($language->text('admin', 'manage_plugins_deactivate_success_message', array('plugin' => $pluginDto->getTitle())));
        $this->redirectToAction('index');
    }

    /**
     * Activates plugin.
     *
     * @param array $params
     */
    public function activate( array $params )
    {
        $pluginDto = $this->getPluginDtoByKey($params);
        $language = PEEP::getLanguage();
        $this->pluginService->activate($pluginDto->getKey());

        // trigger event
        $event = new PEEP_Event(PEEP_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array('pluginKey' => $pluginDto->getKey()));
        PEEP::getEventManager()->trigger($event);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_activate_success_message', array('plugin' => $pluginDto->getTitle())));
        $this->redirectToAction('index');
    }

    public function updateRequest( array $params )
    {
        $this->checkXP();
        $pluginDto = $this->getPluginDtoByKey($params);
        $language = PEEP::getLanguage();

        $remotePluginInfo = (array) $this->pluginService->getItemInfoForUpdate($pluginDto->getKey(), $pluginDto->getDeveloperKey());

        if ( empty($remotePluginInfo) || !empty($remotePluginInfo['error']) )
        {
            $this->assign('mode', 'error');
            $this->assign('text', $language->text('admin', 'plugin_update_request_error'));
            $this->assign('returnUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'index'));
        }
        else if ( (bool) $remotePluginInfo['freeware'] )
        {
            $this->assign('mode', 'free');
            $this->assign('text', $language->text('admin', 'free_plugin_request_text', array('oldVersion' => $pluginDto->getBuild(), 'newVersion' => $remotePluginInfo['build'], 'name' => $pluginDto->getTitle())));
            $this->assign('redirectUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'update', $params));
            $this->assign('returnUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'index'));
        }
        else
        {
            if ( $pluginDto->getLicenseKey() != null )
            {
                $result = $this->pluginService->checkLicenseKey($pluginDto->getKey(), $pluginDto->getDeveloperKey(), $pluginDto->getLicenseKey());
                if ( $result === true )
                {
                    $params['licenseKey'] = $pluginDto->getLicenseKey();
                    $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'update', $params));
                    return;
                }
            }

            $this->assign('text', $language->text('admin', 'com_plugin_request_text', array('oldVersion' => $pluginDto->getBuild(), 'newVersion' => $remotePluginInfo['build'], 'name' => $pluginDto->getTitle())));

            $form = new Form('license-key');

            $licenseKey = new TextField('key');
            $licenseKey->setValue($pluginDto->getLicenseKey());
            $licenseKey->setRequired();
            $licenseKey->setLabel($language->text('admin', 'com_plugin_request_key_label'));
            $form->addElement($licenseKey);

            $submit = new Submit('submit');
            $submit->setValue($language->text('admin', 'license_form_submit_label'));
            $form->addElement($submit);

            $button = new Button('button');
            $button->setValue($language->text('admin', 'license_form_leave_label'));
            $button->addAttribute('onclick', "window.location='" . PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'index') . "'");
            $form->addElement($button);

            $this->addForm($form);

            if ( PEEP::getRequest()->isPost() )
            {
                if ( $form->isValid($_POST) )
                {
                    $data = $form->getValues();
                    $params['licenseKey'] = $data['key'];

                    $result = $this->pluginService->checkLicenseKey($pluginDto->getKey(), $pluginDto->getDeveloperKey(), $data['key']);

                    if ( $result === true )
                    {
                        $pluginDto->setLicenseKey($data['key']);
                        BOL_PluginService::getInstance()->savePlugin($pluginDto);

                        $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'update', $params));
                    }
                    else
                    {
                        PEEP::getFeedback()->error($language->text('admin', 'plugins_manage_invalid_license_key_error_message'));
                        $this->redirect();
                    }
                }
            }
        }
    }

    public function update( array $params )
    {
        $this->checkXP();
        $pluginDto = $this->getPluginDtoByKey($params);

        if ( !empty($_GET['mode']) )
        {
            switch ( trim($_GET['mode']) )
            {
                case 'plugin_up_to_date':
                    PEEP::getFeedback()->warning(PEEP::getLanguage()->text('admin', 'manage_plugins_up_to_date_message'));
                    break;

                case 'plugin_update_success':
                    if ( $pluginDto !== null )
                    {
                        $event = new PEEP_Event(PEEP_EventManager::ON_AFTER_PLUGIN_UPDATE, array('pluginKey' => $pluginDto->getKey()));
                        PEEP::getEventManager()->trigger($event);
                    }

                    PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_update_success_message'));
                    break;

                default :
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_update_process_error'));
                    break;
            }

            $this->redirectToAction('index');
        }

        $ftp = $this->getFtpConnection();

        try
        {
            $archivePath = $this->pluginService->downloadItem($pluginDto->getKey(), $pluginDto->getDeveloperKey(), (!empty($params['licenseKey']) ? $params['licenseKey'] : null));
        }
        catch ( Exception $e )
        {
            PEEP::getFeedback()->error($e->getMessage());
            $this->redirectToAction('index');
        }

        if ( !file_exists($archivePath) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'plugin_update_download_error'));
            $this->redirectToAction('index');
        }

        $zip = new ZipArchive();

        $tempDir = PEEP_DIR_PLUGINFILES . 'peep' . DS . uniqid('plugin_update') . DS;

        if ( $zip->open($archivePath) === true )
        {
            $zip->extractTo($tempDir);
            $zip->close();
        }
        else
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'plugin_update_download_error'));
            $this->redirectToAction('index');
        }

        if ( file_exists($tempDir . 'plugin.xml') )
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

            if ( !empty($innerDir) && file_exists($tempDir . $innerDir . DS . 'plugin.xml') )
            {
                $localDir = $tempDir . $innerDir;
            }
            else
            {
                PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'plugin_update_download_error'));
                $this->redirectToAction('index');
            }
        }


        if ( substr($name, -1) === DS )
        {
            $name = substr($name, 0, (strlen($name) - 1));
        }

        $remoteDir = PEEP_DIR_PLUGIN . $pluginDto->getModule();

        if ( !file_exists($remoteDir) )
        {
            $ftp->mkDir($remoteDir);
        }

        $ftp->uploadDir($localDir, $remoteDir);
        UTIL_File::removeDir($localDir);

        // copy static dir
        $pluginStaticDir = PEEP_DIR_PLUGIN . $pluginDto->getModule() . DS . 'static' . DS;

        if ( !defined('PEEP_PLUGIN_XP') && file_exists($pluginStaticDir) )
        {
            $staticDir = PEEP_DIR_STATIC_PLUGIN . $pluginDto->getModule() . DS;

            if ( !file_exists($staticDir) )
            {
                mkdir($staticDir);
                chmod($staticDir, 0777);
            }

            UTIL_File::copyDir($pluginStaticDir, $staticDir);
        }

        $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP_URL_HOME . 'upgrade/index.php', array('plugin' => $pluginDto->getKey(), 'back-uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
    }

    public function manualUpdateRequest( array $params )
    {
        $this->checkXP();
        $pluginDto = $this->getPluginDtoByKey($params);

        if ( !empty($_GET['mode']) )
        {
            switch ( trim($_GET['mode']) )
            {
                case 'plugin_up_to_date':
                    PEEP::getFeedback()->warning(PEEP::getLanguage()->text('admin', 'manage_plugins_up_to_date_message'));
                    break;

                case 'plugin_update_success':

                    if ( $pluginDto !== null )
                    {
                        $event = new PEEP_Event(PEEP_EventManager::ON_AFTER_PLUGIN_UPDATE, array('pluginKey' => $pluginDto->getKey()));
                        PEEP::getEventManager()->trigger($event);
                    }

                    PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_update_success_message'));
                    break;

                default :
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_update_process_error'));
                    break;
            }

            $this->redirectToAction('index');
        }

        if ( (int) $pluginDto->getUpdate() !== 2 )
        {
            $this->redirectToAction('index');
        }

        $this->assign('text', PEEP::getLanguage()->text('admin', 'manage_plugins_manual_update_request', array('name' => $pluginDto->getTitle())));
        $this->assign('redirectUrl', PEEP::getRequest()->buildUrlQueryString(PEEP_URL_HOME . 'upgrade/index.php', array('plugin' => $pluginDto->getKey(), 'back-uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
    }

    public function coreUpdateRequest()
    {
        $this->checkXP();
        if ( !(bool) PEEP::getConfig()->getValue('base', 'update_soft') )
        {
            throw new Redirect404Exception();
        }

        $newCoreInfo = $this->pluginService->getCoreInfoForUpdate();
        $this->assign('text', PEEP::getLanguage()->text('admin', 'manage_plugins_core_update_request_text', array('oldVersion' => PEEP::getConfig()->getValue('base', 'soft_version'), 'newVersion' => $newCoreInfo['version'], 'info' => $newCoreInfo['info'])));
        $this->assign('redirectUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'coreUpdate'));
        $this->assign('returnUrl', PEEP::getRouter()->urlForRoute('admin_default'));
    }

    public function coreUpdate()
    {
        $this->checkXP();
        if ( !(bool) PEEP::getConfig()->getValue('base', 'update_soft') )
        {
            throw new Redirect404Exception();
        }

        $language = PEEP::getLanguage();

        $archivePath = PEEP_DIR_PLUGINFILES . 'peep' . DS . 'core.zip';

        $tempDir = PEEP_DIR_PLUGINFILES . 'peep' . DS . 'core' . DS;

        $ftp = $this->getFtpConnection();

        $errorMessage = false;

        PEEP::getApplication()->setMaintenanceMode(true);
        $this->pluginService->downloadCore($archivePath);

        if ( !file_exists($archivePath) )
        {
            $errorMessage = $language->text('admin', 'core_update_download_error');
        }
        else
        {
            mkdir($tempDir);

            $zip = new ZipArchive();

            $zopen = $zip->open($archivePath);

            if ( $zopen === true )
            {
                $zip->extractTo($tempDir);
                $zip->close();
                $ftp->uploadDir($tempDir, PEEP_DIR_ROOT);
                $ftp->chmod(0777, PEEP_DIR_STATIC);
                $ftp->chmod(0777, PEEP_DIR_STATIC_PLUGIN);
            }
            else
            {
                $errorMessage = $language->text('admin', 'core_update_unzip_error');
            }
        }

        if ( file_exists($tempDir) )
        {
            UTIL_File::removeDir($tempDir);
        }

        if ( file_exists($archivePath) )
        {
            unlink($archivePath);
        }

        if ( $errorMessage !== false )
        {
            PEEP::getApplication()->setMaintenanceMode(false);
            PEEP::getFeedback()->error($errorMessage);
            $this->redirect(PEEP::getRouter()->urlFor('ADMIN_CTRL_Index', 'index'));
        }

        $this->redirect(PEEP_URL_HOME . 'upgrade/index.php');
    }

    /**
     * Installs plugin.
     *
     * @param array $params
     */
    public function install( array $params )
    {
        if ( empty($params['key']) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_install_empty_key_error_message'));
            $this->redirectToAction('available');
        }

        try
        {
            $pluginDto = $this->pluginService->install(trim($params['key']));
            PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_install_success_message', array('plugin' => $pluginDto->getTitle())));
        }
        catch ( LogicException $e )
        {
            if ( PEEP_DEBUG_MODE )
            {
                throw $e;
            }

            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_install_error_message', array('key' => ( empty($pluginDto) ? '_INVALID_' : $pluginDto->getKey()))));
        }

        $this->redirectToAction('index');
    }

    /**
     * Deletes plugin.
     *
     * @param array $params
     */
    public function uninstall( array $params )
    {
        if ( empty($params['key']) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirectToAction('index');
        }

        $pluginDto = $this->getPluginDtoByKey($params);

        if ( $pluginDto === null )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirectToAction('index');
        }

        try
        {
            $this->pluginService->uninstall($pluginDto->getKey());
        }
        catch ( Exception $e )
        {
            if ( PEEP_DEBUG_MODE )
            {
                throw $e;
            }

            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirectToAction('index');
        }

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_success_message', array('plugin' => $pluginDto->getTitle())));

        $this->redirectToAction('index');
    }

    public function uninstallRequest( array $params )
    {
        if ( empty($params['key']) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirectToAction('index');
        }

        $pluginDto = $this->getPluginDtoByKey($params);
        $language = PEEP::getLanguage();

        if ( $pluginDto === null )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_uninstall_error_message'));
            $this->redirectToAction('index');
        }

        $this->assign('text', $language->text('admin', 'plugin_uninstall_request_text', array('name' => $pluginDto->getTitle())));
        $this->assign('redirectUrl', PEEP::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'uninstall', $params));
    }

    /**
     * Deletes plugin.
     *
     * @param array $params
     */
    public function delete( array $params )
    {
        $this->checkXP();

        $ftp = $this->getFtpConnection();

        $key = trim($params['key']);
        $availablePlugins = $this->pluginService->getAvailablePluginsList();

        if ( !isset($availablePlugins[$key]) )
        {
            PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_plugin_not_found'));
            $this->redirectToAction('available');
        }

        $ftp->rmDir($availablePlugins[$key]['path']);

        PEEP::getFeedback()->info(PEEP::getLanguage()->text('admin', 'manage_plugins_delete_success_message', array('plugin' => $availablePlugins[$key]['title'])));
        $this->redirectToAction('available');
    }

    public function ftpAttrs()
    {
        $this->checkXP();

        $language = PEEP::getLanguage();

        $this->setPageHeading($language->text('admin', 'page_title_manage_plugins_ftp_info'));
        $this->setPageHeadingIconClass('peep_ic_gear_wheel');

        $form = new Form('ftp');

        $login = new TextField('host');
        $login->setValue('localhost');
        $login->setRequired(true);
        $login->setLabel($language->text('admin', 'plugins_manage_ftp_form_host_label'));
        $form->addElement($login);

        $login = new TextField('login');
        $login->setHasInvitation(true);
        $login->setInvitation('login');
        $login->setRequired(true);
        $login->setLabel($language->text('admin', 'plugins_manage_ftp_form_login_label'));
        $form->addElement($login);

        $password = new PasswordField('password');
        $password->setHasInvitation(true);
        $password->setInvitation('password');
        $password->setRequired(true);
        $password->setLabel($language->text('admin', 'plugins_manage_ftp_form_password_label'));
        $form->addElement($password);

        $port = new TextField('port');
        $port->setValue(21);
        $port->addValidator(new IntValidator());
        $port->setLabel($language->text('admin', 'plugins_manage_ftp_form_port_label'));
        $form->addElement($port);

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'plugins_manage_ftp_form_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                PEEP::getSession()->set('ftpAttrs', array('host' => trim($data['host']), 'login' => trim($data['login']), 'password' => trim($data['password']), 'port' => (int) $data['port']));
                if ( !empty($_GET['back_uri']) )
                {
                    $this->redirect(PEEP_URL_HOME . urldecode($_GET['back_uri']));
                }
                else
                {
                    $this->redirectToAction('index');
                }
            }
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
            $ftp = $this->pluginService->getFtpConnection();
        }
        catch ( LogicException $e )
        {
            PEEP::getFeedback()->error($e->getMessage());
            $this->redirect(PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor(__CLASS__, 'ftpAttrs'), array('back_uri' => urlencode(PEEP::getRequest()->getRequestUri()))));
        }

        return $ftp;
    }

    private function getPluginDtoByKey( $params )
    {
        if ( !empty($params['key']) )
        {
            $pluginDto = $this->pluginService->findPluginByKey(trim($params['key']));
        }

        if ( !empty($pluginDto) )
        {
            return $pluginDto;
        }

        PEEP::getFeedback()->error(PEEP::getLanguage()->text('admin', 'manage_plugins_plugin_not_found'));
        $this->redirectToAction('index');
    }

    private function checkXP()
    {
        if ( defined('PEEP_PLUGIN_XP') )
        {
            throw new Redirect404Exception();
        }
    }
}
