<?php

class PHOTO_CMP_AjaxUpload extends PEEP_Component
{
    public function __construct( $albumId = null, $albumName = null, $albumDescription = null, $url = null, $data = null )
    {
        parent::__construct();

        if ( !PEEP::getUser()->isAuthorized('photo', 'upload') )
        {
            $this->setVisible(false);
            
            return;
        }
        
        $userId = PEEP::getUser()->getId();
        $document = PEEP::getDocument();
        
        PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteUserTemporaryPhotos($userId);

        $plugin = PEEP::getPluginManager()->getPlugin('photo');

        $document->addStyleSheet($plugin->getStaticCssUrl() . 'photo_upload.css');
        $document->addScript($plugin->getStaticJsUrl() . 'codemirror.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'upload.js');
        
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.ajaxPhotoUploadParams = Object.freeze({$params});',
                array(
                    'params' => array(
                        'actionUrl' => PEEP::getRouter()->urlForRoute('photo.ajax_upload'),
                        'maxFileSize' => PHOTO_BOL_PhotoService::getInstance()->getMaxUploadFileSize(),
                        'deleteAction' => PEEP::getRouter()->urlForRoute('photo.ajax_upload_delete')
                    )
                )
            )
        );
        $document->addOnloadScript(';window.ajaxPhotoUploader.init();');

        $form = new PHOTO_CLASS_AjaxUploadForm('user', $userId, $albumId, $albumName, $albumDescription, $url, $data);
        $this->addForm($form);
        $this->assign('extendInputs', $form->getExtendedElements());
        $this->assign('albumId', $albumId);
        $this->assign('userId', $userId);

        $cnewsAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getCnewsAlbum($userId);
        $exclude = !empty($cnewsAlbum) ? array($cnewsAlbum->id) : array();
        $this->addComponent('albumNames', PEEP::getClassInstance('PHOTO_CMP_AlbumNameList', $userId, $exclude));
        
        $language = PEEP::getLanguage();
        $language->addKeyForJs('photo', 'not_all_photos_uploaded');
        $language->addKeyForJs('photo', 'size_limit');
        $language->addKeyForJs('photo', 'type_error');
        $language->addKeyForJs('photo', 'dnd_support');
        $language->addKeyForJs('photo', 'dnd_not_support');
        $language->addKeyForJs('photo', 'drop_here');
        $language->addKeyForJs('photo', 'please_wait');
        $language->addKeyForJs('photo', 'create_album');
        $language->addKeyForJs('photo', 'album_name');
        $language->addKeyForJs('photo', 'album_desc');
        $language->addKeyForJs('photo', 'describe_photo');
        $language->addKeyForJs('photo', 'photo_upload_error');
    }
}
