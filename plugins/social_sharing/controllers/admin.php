<?php

class SOCIALSHARING_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public $contentMenu = null;

    public function __construct()
    {
        parent::__construct();
//
//        $language = PEEP::getLanguage();
//
//        $router = PEEP_Router::getInstance();
//
//        $menuItems = array();
//
//        $menuItem = new BASE_MenuItem();
//        $menuItem->setKey('socialsharing_index');
//        $menuItem->setLabel($language->text('socialsharing', 'socialsharing_items'));
//        $menuItem->setUrl($router->urlForRoute('socialsharing.admin'));
//        $menuItem->setOrder('1');
//        $menuItem->setIconClass('peep_ic_gear_wheel');
//
//        $menuItems[] = $menuItem;
//
//        $menuItem = new BASE_MenuItem();
//        $menuItem->setKey('socialsharing_default_image');
//        $menuItem->setLabel($language->text('socialsharing', 'socialsharing_default_image'));
//        $menuItem->setUrl($router->urlForRoute('socialsharing.default_image'));
//        $menuItem->setOrder('2');
//        $menuItem->setIconClass('peep_ic_files');
//
//        $menuItems[] = $menuItem;
//
//        $this->contentMenu = new BASE_CMP_ContentMenu($menuItems);
//
//        $this->addComponent('contentMenu', $this->contentMenu);
    }
    

    public function index()
    {
        //$this->contentMenu->getElement('socialsharing_index')->setActive(true);
    	$config = PEEP::getConfig();

        $order = $config->getValue('socialsharing', 'order');
        $defautOrder = SOCIALSHARING_CLASS_Settings::getEntityList();

        if ( !empty($order) )
        {
            $order = json_decode($order, true);

            if( !is_array($order) )
            {
                $order = $defautOrder;
            }

            $result = array();
            foreach ( $order as $key => $item )
            {
                if ( in_array($key, $defautOrder) )
                {
                    $result[$key] = $key;
                }
            }

            if ( !empty($order) )
            {
                $order = $result;
            }
            else
            {
                $order = $defautOrder;
            }
        }
        else
        {
            $order = $defautOrder;
        }
        
        $this->assign('order', $order);
        $this->assign('values', PEEP::getConfig()->getValues('socialsharing'));
        
        $upload = new Form('upload');
        $upload->setEnctype("multipart/form-data");
        
        $file = new FileField('image');
        $validator = new SocialSharingImageValidator(true);
        $file->addValidator($validator);
        $upload->addElement($file);

        $submit = new Submit('upload_image');
        $submit->setValue(PEEP::getLanguage()->text('socialsharing', 'upload_image_button_label'));
        $upload->addElement($submit);

        $apiKeyForm = new Form('api_key_form');

        $key = new TextField('api_key');
        $key->setLabel(PEEP::getLanguage()->text('socialsharing', 'api_key_label'));
        $key->setRequired();

        $apiKey = PEEP::getConfig()->getValue('socialsharing', 'api_key');
        $key->setValue($apiKey);

        $apiKeyForm->addElement($key);

        $apiKeySubmit = new Submit('save_api_key');
        $apiKeyForm->addElement($apiKeySubmit);

        $this->addForm($apiKeyForm);

        if ( PEEP::getRequest()->isPost() )
        {
            if ( isset($_POST['upload_image']) )
            {
                if ( $upload->isValid($_POST) )
                {
                    SOCIALSHARING_BOL_Service::getInstance()->uploadImage($_FILES['image']['tmp_name']);

                    PEEP::getFeedback()->info(PEEP::getLanguage()->text('socialsharing', 'image_upload_success')); //Image succsessfully uploaded
                    $this->redirect();
                }
                else
                {
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('socialsharing', 'image_upload_error')); //
                }
            }
            else if ( isset($_POST['save_api_key']) )
            {
                if ( $apiKeyForm->isValid($_POST) )
                {
                    $data = $apiKeyForm->getValues();
                    $config->saveConfig('socialsharing', 'api_key', $data['api_key']);

                    PEEP::getFeedback()->info(PEEP::getLanguage()->text('socialsharing', 'api_key_saved')); //Setting succsessfully saved
                    $this->redirect();
                }
                else
                {
                    PEEP::getFeedback()->error(PEEP::getLanguage()->text('socialsharing', 'settings_saved_error'));
                }
            }
        }

        $this->addForm($upload);

        $this->assign('imageUrl', SOCIALSHARING_BOL_Service::getInstance()->getDefaultImageUrl() . '?pid=' . md5(rand(0, 9999999999)) );

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl()."jquery-ui.min.js");
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('socialsharing')->getStaticJsUrl()."admin.js");
        PEEP::getDocument()->addOnloadScript(" window.sharing = new socialSharingAdmin(".json_encode( array( "ajaxResponderUrl" => PEEP::getRouter()->urlFor('SOCIALSHARING_CTRL_Admin', 'ajaxResponder') ) ).") ");
    }

//    public function defaultImage()
//    {
//        $this->contentMenu->getElement('socialsharing_default_image')->setActive(true);
//
//        $settings = new Form('default_image');
//        $settings->setEnctype("multipart/form-data");
//
//        $file = new FileField('image');
//        $validator = new SocialSharingImageValidator(true);
//        $file->addValidator($validator);
//        $settings->addElement($file);
//
//        $submit = new Submit('upload');
//        $submit->setValue(PEEP::getLanguage()->text('socialsharing', 'upload'));
//        $settings->addElement($submit);
//        //printVar($config->getValue('socialsharing', 'facebook'));
//        if ( PEEP::getRequest()->isPost() )
//        {
//            if ( $settings->isValid($_POST) )
//            {
//                SOCIALSHARING_BOL_Service::getInstance()->uploadImage($_FILES['image']['tmp_name']);
//                PEEP::getFeedback()->info(PEEP::getLanguage()->text('socialsharing', 'upload_complite')); //Setting succsessfully saved
//            }
//            else
//            {
//                $message = PEEP::getLanguage()->text('base', 'upload_file_fail');
//
//                switch ( $_FILES['image']['error'] )
//                {
//                    case UPLOAD_ERR_INI_SIZE:
//                        $message = $language->text('base', 'upload_file_max_upload_filesize_error');
//                        break;
//
//                    case UPLOAD_ERR_PARTIAL:
//                        $message = $language->text('base', 'upload_file_file_partially_uploaded_error');
//                        break;
//
//                    case UPLOAD_ERR_NO_FILE:
//                        $message = $language->text('base', 'upload_file_no_file_error');
//                        break;
//
//                    case UPLOAD_ERR_NO_TMP_DIR:
//                        $message = $language->text('base', 'upload_file_no_tmp_dir_error');
//                        break;
//
//                    case UPLOAD_ERR_CANT_WRITE:
//                        $message = $language->text('base', 'upload_file_cant_write_file_error');
//                        break;
//
//                    case UPLOAD_ERR_EXTENSION:
//                        $message = $language->text('base', 'upload_file_invalid_extention_error');
//                        break;
//                }
//
//                PEEP::getFeedback()->error($message);
//            }
//            $this->redirect();
//        }
//
//        $this->addForm($settings);
//
//        $this->assign('imageUrl', SOCIALSHARING_BOL_Service::getInstance()->getDefaultImageUrl());
//    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !PEEP::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'save_settings':                
                    $key = null;
                    
                    if ( empty($_POST['key']) || !in_array($_POST['key'], SOCIALSHARING_CLASS_Settings::getEntityList()) )
                    {
                        echo json_encode(array('result' => false, 'msg' => 'invalid config name'));
                        exit;
                    }
                    
                    $key = $_POST['key'];
                    
                    $value = false;
                    if ( !empty($_POST['value']) )
                    {
                        $value = (boolean)$_POST['value'];
                    }
                    
                    if ( !PEEP::getConfig()->configExists('socialsharing', $key) ) 
                    {
                        echo json_encode(array('result' => false, 'msg' => 'config does not exists'));
                        exit;
                    }
                    
                    PEEP::getConfig()->saveConfig('socialsharing', $key, $value);
                    
                    echo json_encode(array('result' => true));
                    exit;
                    
                break;
            case 'sort_sharing_item':

                $result = false;

                $order = $_POST["order"];

                if ( empty($order) )
                {
                    echo json_encode(array('result' => $result));
                    return;
                }

                $config = PEEP::getConfig();
                $config->saveConfig('socialsharing', 'order', $order);
                $result = true;
                echo json_encode(array('result' => $result));

                break;

            default:
                echo json_encode(array());
            break;
        }

        exit;
    }
}

class SocialSharingImageValidator extends PEEP_Validator
{
    protected $setRequired = false;

    public function __construct( $setRequired = false )
    {
        $this->setRequired = $setRequired;

        $language = PEEP::getLanguage();
        $this->setErrorMessage($language->text('base', 'not_valid_image'));
    }

    public function isValid( $value )
    {
        $language = PEEP::getLanguage();

        if ( !isset($_FILES['image']['name']) || strlen($_FILES['image']['name']) == 0 )
        {
            $return = false;
            if ( !$this->setRequired )
            {
                $return = true;
            }
            return $return;
        }

        if ( isset($_FILES['image']['name']) && !UTIL_File::validateImage($_FILES['image']['name']) )
        {
            return false;
        }

        if ( $_FILES['image']['error'] != UPLOAD_ERR_OK )
        {
            $message = '';

            switch ( $_FILES['image']['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $message = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $message = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $message = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $message = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $message = $language->text('base', 'upload_file_invalid_extention_error');
                    break;
            }

            if ( !empty($message) )
            {
                $this->setErrorMessage($message);
                return false;
            }
        }

        return true;
    }

    public function getJsValidator()
    {
        $condition = '';

        if ( $this->setRequired )
        {
            $condition = "if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }";
        }

        return "{
                validate : function( value ){ " . $condition . " },
                getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}
