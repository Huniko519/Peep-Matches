<?php

final class BOL_AttachmentService
{
    /**
     * @var BOL_AttachmentDao
     */
    private $attachmentDao;

    /**
     * Singleton instance.
     *
     * @var BOL_AttachmentService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AttachmentService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->attachmentDao = BOL_AttachmentDao::getInstance();
    }

    public function deleteExpiredTempImages()
    {
        $attachList = $this->attachmentDao->findExpiredInactiveItems(time() - 3600);

        /* @var $item BOL_Attachment */
        foreach ( $attachList as $item )
        {
            $filePath = $this->getAttachmentsDir() . $item->getFileName();

            if ( PEEP::getStorage()->fileExists($filePath) )
            {
                PEEP::getStorage()->removeFile($filePath);
            }

            $this->attachmentDao->delete($item);
        }
    }

    public function deleteUserAttachments( $userId )
    {
        $list = $this->attachmentDao->findByUserId($userId);

        /* @var $item BOL_Attachment */
        foreach ( $list as $item )
        {
            $filePath = $this->getAttachmentsDir() . $item->getFileName();

            if ( PEEP::getStorage()->fileExists($filePath) )
            {
                PEEP::getStorage()->removeFile($filePath);
            }

            $this->attachmentDao->delete($item);
        }
    }

    // TODO refactor - should delete attachment by id
//    public function deleteAttachmentByUrl( $url )
//    {
//        $attch = $this->attachmentDao->findAttachmentByFileName(trim(basename($url)));
//
//        if ( $attch != NULL )
//        {
//            if ( PEEP::getStorage()->fileExists($this->getAttachmentsDir() . $attch->getFileName()) )
//            {
//                PEEP::getStorage()->removeFile($this->getAttachmentsDir() . $attch->getFileName());
//            }
//
//            $this->attachmentDao->delete($attch);
//        }
//        else
//        {
//            if ( PEEP::getStorage()->fileExists($this->getAttachmentsDir() . basename($url)) )
//            {
//                PEEP::getStorage()->removeFile($this->getAttachmentsDir() . basename($url));
//            }
//        }
//    }

    public function deleteAttachmentById( $id )
    {
        $attch = $this->attachmentDao->findById($id);
        /* @var $attch BOL_Attachment */
        if ( $attch !== null )
        {
            PEEP::getStorage()->removeFile($this->getAttachmentsDir() . $attch->getFileName());
            $this->attachmentDao->delete($attch);
        }
    }

//    public function saveTempImage( $id )
//    {
//        $attch = $this->attachmentDao->findById($id);
//        /* @var $attch BOL_Attachment */
//        if ( $attch === null )
//        {
//            return '_INVALID_URL_';
//        }
//
//        $filePath = $this->getAttachmentsTempDir() . $attch->getFileName();
//
//        if ( PEEP::getUser()->isAuthenticated() && file_exists($filePath) )
//        {
//            PEEP::getStorage()->copyFile($filePath, $this->getAttachmentsDir() . $attch->getFileName());
//            unlink($filePath);
//        }
//
//        $attch->setStatus(true);
//        $this->attachmentDao->save($attch);
//
//        return PEEP::getStorage()->getFileUrl($this->getAttachmentsDir() . $attch->getFileName());
//    }
    /*
     * @param array $fileInfo
     * @return array
     */

    public function processPhotoAttachment( $pluginKey, array $fileInfo, $bundle = null, $validFileExtensions = array(), $maxUploadSize = 0 )
    {
        return $this->processUploadedFile($pluginKey, $fileInfo, $bundle, array('jpeg', 'jpg', 'png', 'gif'), (float) PEEP::getConfig()->getValue('base', 'tf_max_pic_size') * 1024);
    }
    /* attachments 1.6.1 update */

//    public function getAttachmentsTempUrl()
//    {
//        return PEEP::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'attachments/temp/';
//    }
//
//    public function getAttachmentsTempDir()
//    {
//        return PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS . 'temp' . DS;
//    }

    public function getAttachmentsUrl()
    {
        return PEEP::getStorage()->getFileUrl(PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments') . '/';
    }

    public function getAttachmentsDir()
    {
        return PEEP::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS;
    }

    public function saveAttachment( BOL_Attachment $dto )
    {
        $this->attachmentDao->save($dto);
    }

    public function processUploadedFile( $pluginKey, array $fileInfo, $bundle = null, $validFileExtensions = array(), $maxUploadSize = null, $dimensions = null )
    {
        $language = PEEP::getLanguage();
        $error = false;

        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new InvalidArgumentException($language->text('base', 'user_is_not_authenticated'));
        }

        if ( empty($fileInfo) || !is_uploaded_file($fileInfo['tmp_name']) )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_fail'));
        }

        if ( $fileInfo['error'] != UPLOAD_ERR_OK )
        {
            switch ( $fileInfo['error'] )
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

            throw new InvalidArgumentException($error);
        }

        if ( empty($validFileExtensions) )
        {
            $validFileExtensions = json_decode(PEEP::getConfig()->getValue('base', 'attch_ext_list'), true);
        }

        if ( $maxUploadSize === null )
        {
            $maxUploadSize = PEEP::getConfig()->getValue('base', 'attch_file_max_size_mb');
        }

        if ( !empty($validFileExtensions) && !in_array(UTIL_File::getExtension($fileInfo['name']), $validFileExtensions) )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_extension_is_not_allowed'));
        }

        // get all bundle upload size
        $bundleSize = floor($fileInfo['size'] / 1024);
        if ( $bundle !== null )
        {
            $list = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

            /* @var $item BOL_Attachment */
            foreach ( $list as $item )
            {
                $bundleSize += $item->getSize();
            }
        }

        if ( $maxUploadSize > 0 && $bundleSize > ($maxUploadSize * 1024) )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_max_upload_filesize_error'));
        }

        $attachDto = new BOL_Attachment();
        $attachDto->setUserId(PEEP::getUser()->getId());
        $attachDto->setAddStamp(time());
        $attachDto->setStatus(0);
        $attachDto->setSize(floor($fileInfo['size'] / 1024));
        $attachDto->setOrigFileName(htmlspecialchars($fileInfo['name']));
        $attachDto->setFileName(uniqid() . '_' . UTIL_File::sanitizeName($attachDto->getOrigFileName()));
        $attachDto->setPluginKey($pluginKey);


        if ( $bundle !== null )
        {
            $attachDto->setBundle($bundle);
        }

        $this->attachmentDao->save($attachDto);
        $uploadPath = $this->getAttachmentsDir() . $attachDto->getFileName();
        $tempPath = $this->getAttachmentsDir() . 'temp_' . $attachDto->getFileName();

        if ( in_array(UTIL_File::getExtension($fileInfo['name']), array('jpg', 'jpeg', 'gif', 'png')) )
        {
            try
            {
                $image = new UTIL_Image($fileInfo['tmp_name']);

                if ( empty($dimensions) )
                {
                    $dimensions = array('width' => 1000, 'height' => 1000);
                }

                $image->resizeImage($dimensions['width'], $dimensions['height'])->orientateImage()->saveImage($tempPath);
                $image->destroy();
                @unlink($fileInfo['tmp_name']);
            }
            catch ( Exception $e )
            {
                throw new InvalidArgumentException($language->text('base', 'upload_file_fail'));
            }
        }
        else
        {
            move_uploaded_file($fileInfo['tmp_name'], $tempPath);
        }

        PEEP::getStorage()->copyFile($tempPath, $uploadPath);
        PEEP::getStorage()->chmod($uploadPath, 0666);
        unlink($tempPath);

        return array('uid' => $attachDto->getBundle(), 'dto' => $attachDto, 'path' => $uploadPath, 'url' => $this->getAttachmentsUrl() . $attachDto->getFileName());
    }

    public function getFilesByBundleName( $pluginKey, $bundle )
    {
        $list = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

        $resultArray = array();

        /* @var $item BOL_Attachment */
        foreach ( $list as $item )
        {
            $resultArray[] = array('dto' => $item, 'path' => $this->getAttachmentsDir() . $item->getFileName(), 'url' => $this->getAttachmentsUrl() . $item->getFileName());
        }

        return $resultArray;
    }

    /**
     * @param string $bundle
     * @param int $status
     */
    public function updateStatusForBundle( $pluginKey, $bundle, $status )
    {
        $this->attachmentDao->updateStatusByBundle($pluginKey, $bundle, $status);
    }

    /**
     * @param int $userId
     * @param int $attachId
     */
    public function deleteAttachment( $userId, $attachId )
    {
        /* @var $attachId BOL_Attachment */
        $attach = $this->attachmentDao->findById($attachId);

        if ( $attach !== null && PEEP::getUser()->isAuthenticated() && PEEP::getUser()->getId() == $attach->getUserId() )
        {
            PEEP::getStorage()->removeFile($this->getAttachmentsDir() . $attach->getFileName());
            $this->attachmentDao->delete($attach);
        }
    }

    /**
     * @param string $pluginKey
     * @param string $bundle
     */
    public function deleteAttachmentByBundle( $pluginKey, $bundle )
    {
        $attachments = $this->attachmentDao->findAttahcmentByBundle($pluginKey, $bundle);

        /* @var $attach BOL_Attachment */
        foreach ( $attachments as $attach )
        {
            PEEP::getStorage()->removeFile($this->getAttachmentsDir() . $attach->getFileName());
            $this->attachmentDao->delete($attach);
        }
    }
}
