<?php

class BASE_CTRL_MediaPanel extends PEEP_ActionController
{
    /**
     * @var BASE_CMP_Menu
     */
    private $menu;
    private $id;

    public function __construct()
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
            return;
        }

        if ( !PEEP::getRequest()->isAjax() ) 
        {
            PEEP::getDocument()->getMasterPage()->setTemplate(PEEP::getThemeManager()->getMasterPageTemplate(PEEP_MasterPage::TEMPLATE_BLANK));
            PEEP::getDocument()->addStyleDeclaration(".peep_footer{display:none;}");
        }
    }

    public function ajaxUpload( $params )
    {
        $pluginKey = $params['pluginKey'];
        $result = array();

        if (PEEP::getRequest()->isPost())
        {
            if ( !empty($_POST['command']) && $_POST['command'] == 'image-upload' )
            {
                $imageId = UploadImageForm::addFile($pluginKey);

                if ( is_numeric($imageId) )
                {
                    $img = BOL_MediaPanelService::getInstance()->findImage($imageId);
                    $url = PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->
                            getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name);

                    $result = array(
                        'file_url' => PEEP::getStorage()->
                                getFileUrl(PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name),
                    );
                }
                else {
                    $result = array(
                        'error_message' => $imageId,
                    );
                }
            }
        }

        die(json_encode($result));
    }

    public function index( $params )
    {
        $pluginKey = $params['pluginKey'];

        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->menu->getElement('upload')->setActive(true);

        $form = new UploadImageForm();

        if ( !empty($_POST['command']) && $_POST['command'] == 'image-upload' )
        {
            UploadImageForm::process($pluginKey, $params);
        }

        $this->assign('maxSize', PEEP::getConfig()->getValue('base', 'tf_max_pic_size'));
        
        $this->addForm($form);
    }

    public function gallery( $params )
    {

        if ( PEEP::getRequest()->isPost() )
        {
            $userId = PEEP::getUser()->getId();
            if ( empty($userId) )
            {
                throw new Exception('Guests can\'t view this page');
            }

            $imgId = intval($_POST['img-id']);

            if ( $imgId <= 0 )
            {
                throw new Redirect404Exception();
            }

            BOL_MediaPanelService::getInstance()->deleteById($imgId);

            PEEP::getFeedback()->info(PEEP::getLanguage()->text('base', 'media_panel_file_deleted'));
            $this->redirect();
        }

        $pluginKey = $params['pluginKey'];
        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->menu->getElement('gallery');

        $service = BOL_MediaPanelService::getInstance();

        $list = $service->findGalleryImages($pluginKey, PEEP::getUser()->getId(), 0, 500);
        $list = array_reverse($list);
        $images = array();

        foreach ( $list as $img )
        {
            $images[] = array(
                'dto' => $img,
                'data' => $img->getData(),
                'url' => PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name),
                'sel' => !empty($params['pid']) && $img->getId() == $params['pid'],
            );
        }

        $this->assign('images', $images);
        $this->assign('id', $params['id']);
    }

    public function fromUrl( $params )
    {
        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->assign('elid', $params['id']);
    }

    private function initMenu( $params )
    {
        $language = PEEP::getLanguage();
        $router = PEEP::getRouter();

        $this->menu = new BASE_CMP_ContentMenu();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'upload'));
        $item->setOrder(0);
        $item->setKey('upload');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'index', $params));
        $this->menu->addElement($item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_from_url'));
        $item->setOrder(1);
        $item->setKey('url');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'fromUrl', $params));
        $this->menu->addElement($item);

        $count = BOL_MediaPanelService::getInstance()->countGalleryImages($params['pluginKey'], PEEP::getUser()->getId());

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_gal') . ($count == 0 ? '' : " ({$count})" ));
        $item->setOrder(1);
        $item->setKey('gallery');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'gallery', $params));
        $this->menu->addElement($item);
    }
}

class UploadImageForm extends Form
{

    public function __construct()
    {
        parent::__construct('image-upload');

        $this->setEnctype('multipart/form-data');

        $hidden = new HiddenField('command');

        $hidden->setValue('image-upload');

        $this->addElement($hidden);

        $hiddenMaxSize = new HiddenField('MAX_FILE_SIZE');

        $hiddenMaxSize->setValue(intval(PEEP::getConfig()->getValue('base', 'tf_max_pic_size')) * 1000000);

        $fileInput = new FileField('file');

        $fileInput->setLabel(PEEP::getLanguage()->text('base', 'tf_img_choose_file'))->setRequired(true);

        $this->addElement($fileInput);

        $submit = new Submit('submit');

        $submit->setValue(PEEP::getLanguage()->text('base', 'upload'));

        $this->addElement($submit);

        return $this;
    }

    /**
     * Add file
     * 
     * @param string $plugin
     * @return integer|string
     */
    public static function addFile( $plugin )
    {
        $uploaddir = PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir();
        $name = $_FILES['file']['name'];

        if ( !UTIL_File::validateImage($name) )
        {
            return PEEP::getLanguage()->text('base', 'invalid_file_type_acceptable_file_types_jpg_png_gif');
        }

        $tmpname = $_FILES['file']['tmp_name'];

        if ( (int) $_FILES['file']['size'] > (float) PEEP::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024 )
        {
            return PEEP::getLanguage()->text('base', 'upload_file_max_upload_filesize_error');
        }

        $image = new UTIL_Image($tmpname);
        $height = $image->getHeight();
        $width = $image->getWidth();

        $id = BOL_MediaPanelService::getInstance()->add($plugin, 'image', PEEP::getUser()->getId(), array('name' => $name, 'height' => $height, 'width' => $width));
        PEEP::getStorage()->copyFile($tmpname, $uploaddir . $id . '-' . $name);
        @unlink($tmpname);

        return $id;
    }

    public static function process( $plugin, $params )
    {
        $imageId = self::addFile($plugin);

        if (!is_numeric($imageId)) {
            PEEP::getFeedback()->error($imageId);
            PEEP::getApplication()->redirect();
        }

        $params['pid'] = $imageId;
        PEEP::getApplication()->redirect(PEEP::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'gallery', $params) . '#bottom');
    }
}
