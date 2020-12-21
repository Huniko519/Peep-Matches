<?php


class EMOTICONS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->service = EMOTICONS_BOL_Service::getInstance();
        
        $language = PEEP::getLanguage();
        
        $language->addKeyForJs('emoticons', 'add_smile_label');
        $language->addKeyForJs('emoticons', 'edit_smile_label');
        $language->addKeyForJs('emoticons', 'are_you_sure');
        $language->addKeyForJs('emoticons', 'add_smile_category');
    }
    
    public function init()
    {
        parent::init();
        
        $general = new BASE_MenuItem();
        $general->setLabel(PEEP::getLanguage()->text('emoticons', 'admin_menu_general'));
        $general->setUrl(PEEP::getRouter()->urlForRoute('emoticons.admin'));
        $general->setKey('general');
        $general->setIconClass('peep_ic_gear_wheel');
        $general->setOrder(0);
        
      
        
        $menu = new BASE_CMP_ContentMenu(array($general));
        $this->addComponent('menu', $menu);
    }

    public function index( array $params = array() )
    {
        $this->assign('emoticonsUrl', $this->service->getEmoticonsUrl());
        $emoticons = array();
        $captions = array();
        
        foreach ( $this->service->getAllEmoticons() as $smile )
        {
            if ( !isset($emoticons[$smile->category]) )
            {
                $emoticons[$smile->category] = array();
            }
            
            $emoticons[$smile->category][] = $smile;
            
            if ( !empty($smile->isCaption) && !isset($captions[$smile->category]) )
            {
                $captions[$smile->category] = $smile->name;
            }
        }
        
        $this->assign('captions', $captions);
        
        if ( count($emoticons) === 1 )
        {
            $keys = array_keys($emoticons);
            $this->assign('emoticons', $emoticons[$keys[0]]);
            $this->assign('isSingle', TRUE);
        }
        else
        {
            $this->assign('emoticons', $emoticons);
            $this->assign('isSingle', FALSE);
        }
    }
    
    public function view( array $params = array() )
    {
        $config = PEEP::getConfig();
        $language = PEEP::getLanguage();
        
        $form = new Form('emoticons-settings');
        
        $width = new TextField('width');
        $width->setRequired();
        $width->addValidator(new IntValidator(1));
        $width->setLabel($language->text('emoticons', 'width_settings_label'));
        
        $submit = new Submit('save');
        $submit->setValue($language->text('emoticons', 'save'));
        
        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $config->saveConfig('emoticons', 'width', (int)$_POST['width']);
        }
        
        $width->setValue((int)$config->getValue('emoticons', 'width'));
        
        $form->addElement($width);
        $form->addElement($submit);
        
        $this->addForm($form);
        
        $this->assign('width', (int)$config->getValue('emoticons', 'width'));
        $this->assign('url', EMOTICONS_BOL_Service::getInstance()->getEmoticonsUrl());
        $emoticons = array();
        $captions = array();
        
        foreach ( EMOTICONS_BOL_Service::getInstance()->getAllEmoticons() as $smile )
        {
            if ( !isset($emoticons[$smile->category]) )
            {
                $emoticons[$smile->category] = array();
            }
            
            $emoticons[$smile->category][] = $smile;
            
            if ( !empty($smile->isCaption) && !isset($captions[$smile->category]) )
            {
                $captions[$smile->category] = $smile->name;
            }
        }
        
        $this->assign('captions', $captions);
        
        if ( count($emoticons) === 1 )
        {
            $keys = array_keys($emoticons);
            $this->assign('emoticons', $emoticons[$keys[0]]);
            $this->assign('isSingle', TRUE);
        }
        else
        {
            $this->assign('emoticons', $emoticons);
            $this->assign('isSingle', FALSE);
        }
    }
    
    public function reorder( array $params = array() )
    {
        $this->service->updateEmoticonsOrder($_POST);
    }
    
    public function add( array $params = array() )
    {
        if ( PEEP::getRequest()->isPost() )
        {
            $form = new EMOTICONS_CLASS_AddForm();
            
            if ( $form->isValid($_POST) )
            {
                $ext = UTIL_File::getExtension($_FILES[EMOTICONS_CLASS_AddForm::ELEMENT_FILE]['name']);
                $fileName = uniqid() . '.' . $ext;
                $smileDir = $this->service->getEmoticonsDir();
                move_uploaded_file($_FILES[EMOTICONS_CLASS_AddForm::ELEMENT_FILE]['tmp_name'], $smileDir . $fileName);
                
                $smileEntity = new EMOTICONS_BOL_Emoticons();
                $smileEntity->order = $this->service->getFreeOrder();
                $smileEntity->name = $fileName;
                $smileEntity->code = $this->service->sanitizeCode($form->getElement(EMOTICONS_CLASS_AddForm::ELEMENT_SMILE_CODE)->getValue());
                $smileEntity->isCaption = 0;
                
                if ( $form->getElement(EMOTICONS_CLASS_AddForm::ELEMENT_CATEGORY)->getValue() != NULL )
                {
                    $smileEntity->category = $form->getElement(EMOTICONS_CLASS_AddForm::ELEMENT_CATEGORY)->getValue();
                }
                
                EMOTICONS_BOL_EmoticonsDao::getInstance()->save($smileEntity);
                
                PEEP::getFeedback()->info(PEEP::getLanguage()->text('emoticons', 'success_added_message'));
            }
            else
            {
                foreach ( $form->getErrors() as $errors )
                {
                    foreach ( $errors as $message )
                    {
                        PEEP::getFeedback()->error($message);
                    }
                }
            }
        }
        
        $this->redirect(PEEP::getRouter()->uriForRoute('emoticons.admin'));
    }

    public function edit( array $params = array() )
    {
        if ( PEEP::getRequest()->isPost() && !empty($_POST[EMOTICONS_CLASS_EditForm::ELEMENT_SMILE_ID]) )
        {
            $smileEntity = $this->service->findSmileById($_POST[EMOTICONS_CLASS_EditForm::ELEMENT_SMILE_ID]);
            $form = new EMOTICONS_CLASS_EditForm($smileEntity->id, $smileEntity->code);
            
            if ( $form->isValid($_POST) )
            {
                $smileEntity->code = $this->service->sanitizeCode($form->getElement(EMOTICONS_CLASS_EditForm::ELEMENT_SMILE_CODE)->getValue());
                
                EMOTICONS_BOL_EmoticonsDao::getInstance()->save($smileEntity);
                
                PEEP::getFeedback()->info('Smile successfully updated');
            }
            else
            {
                foreach ( $form->getErrors() as $errors )
                {
                    foreach ( $errors as $message )
                    {
                        PEEP::getFeedback()->error($message);
                    }
                }
            }
        }

        $this->redirect(PEEP::getRouter()->uriForRoute('emoticons.admin'));
    }
    
    public function delete( array $params = array() )
    {
        if ( !empty($_POST['id']) && ($smile = $this->service->findSmileById($_POST['id'])) !== NULL )
        {
            if ( $smile->isCaption )
            {
                exit(json_encode(array(
                    'error' => true,
                    'message' => "You can't delete Category Icon smile"
                )));
            }
            else
            {
                @unlink($this->service->getEmoticonsDir() . $smile->name);
                EMOTICONS_BOL_EmoticonsDao::getInstance()->deleteById($smile->id);
                exit(json_encode(array(
                    'message' => 'Smile successfully deleted'
                )));
            }
        }
    }
    
    public function addCategory( array $params = array() )
    {
        if ( PEEP::getRequest()->isPost() )
        {
            $form = new EMOTICONS_CLASS_AddCategoryForm();
            
            if ( $form->isValid($_POST) )
            {
                $ext = UTIL_File::getExtension($_FILES[EMOTICONS_CLASS_AddForm::ELEMENT_FILE]['name']);
                $fileName = uniqid() . '.' . $ext;
                $smileDir = $this->service->getEmoticonsDir();
                move_uploaded_file($_FILES[EMOTICONS_CLASS_AddForm::ELEMENT_FILE]['tmp_name'], $smileDir . $fileName);
                
                $smileEntity = new EMOTICONS_BOL_Emoticons();
                $smileEntity->order = $this->service->getFreeOrder();
                $smileEntity->name = $fileName;
                $smileEntity->code = $this->service->sanitizeCode($form->getElement(EMOTICONS_CLASS_AddForm::ELEMENT_SMILE_CODE)->getValue());
                $smileEntity->category = $this->service->getFreeSmileCategory();
                $smileEntity->isCaption = 1;
                
                EMOTICONS_BOL_EmoticonsDao::getInstance()->save($smileEntity);
                
                PEEP::getFeedback()->info('Category successfully added');
            }
            else
            {
                foreach ( $form->getErrors() as $errors )
                {
                    foreach ( $errors as $message )
                    {
                        PEEP::getFeedback()->error($message);
                    }
                }
            }
        }
        
        $this->redirect(PEEP::getRouter()->uriForRoute('emoticons.admin'));
    }
    
    public function deleteCategory( array $params = array() )
    {
        if ( !empty($_POST['id']) )
        {
            $emoticons = $this->service->findEmoticonsByCategory($_POST['id']);
            $smileDir = $this->service->getEmoticonsDir();
            
            foreach ( $emoticons as $smile )
            {
                @unlink($smileDir . $smile->name);
            }
            
            $this->service->deleteEmoticonsByCategory($_POST['id']);
            exit(json_encode(array(
                'message' => 'Category successfully deleted'
            )));
        }
    }
    
    public function changeCaption( array $params = array() )
    {
        if ( empty($_POST['id']) || empty($_POST['categoryId']) )
        {
            return;
        }
        
        $this->service->setSmileCaption($_POST['id'], $_POST['categoryId']);
    }
}
