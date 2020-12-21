<?php

final class PHOTO_BOL_PhotoTemporaryService
{
    CONST TEMPORARY_PHOTO_LIVE_LIMIT = 86400;
    
    /**
     * @var PHOTO_BOL_PhotoTemporaryDao
     */
    private $photoTemporaryDao;
    /**
     * Class instance
     *
     * @var PHOTO_BOL_PhotoTemporaryService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->photoTemporaryDao = PHOTO_BOL_PhotoTemporaryDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PHOTO_BOL_PhotoTemporaryService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function addTemporaryPhoto( $source, $userId, $order = 0 )
    {
        if ( !file_exists($source) || !$userId )
        {
            return false;
        }
        
        $tmpPhoto = new PHOTO_BOL_PhotoTemporary();
        $tmpPhoto->userId = $userId;
        $tmpPhoto->addDatetime = time();
        $tmpPhoto->hasFullsize = 0;
        $tmpPhoto->order = $order;
        $this->photoTemporaryDao->save($tmpPhoto);
        
        try
        {
            /*
             * Beter performance but less quolity - small size images is slightly clouded
             * 
            $fullscreenTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 5);
            $originalTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 3);
            $mainTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 2);
            $previewTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 1);
            $smallTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 4);
            
            $image = new UTIL_Image($source);
            
            $storeFullSize = PEEP::getConfig()->getValue('photo', 'store_fullsize');
            
            if ( $storeFullSize )
            {   
                $image->resizeImage(PHOTO_BOL_PhotoService::DIM_FULLSCREEN_WIDTH, PHOTO_BOL_PhotoService::DIM_FULLSCREEN_HEIGHT)
                    ->orientateImage()
                    ->saveImage($fullscreenTmp);
                
                $tmpPhoto->hasFullsize = 1;
            }
            
            $image->resizeImage(PHOTO_BOL_PhotoService::DIM_ORIGINAL_WIDTH, PHOTO_BOL_PhotoService::DIM_ORIGINAL_HEIGHT);
            
            if ( !$storeFullSize )
            {
                $image->orientateImage();
            }
            
            $image->saveImage($originalTmp)
                    ->resizeImage(PHOTO_BOL_PhotoService::DIM_MAIN_WIDTH, PHOTO_BOL_PhotoService::DIM_MAIN_HEIGHT)
                    ->saveImage($mainTmp)
                    ->resizeImage(PHOTO_BOL_PhotoService::DIM_PREVIEW_WIDTH, PHOTO_BOL_PhotoService::DIM_PREVIEW_HEIGHT)
                    ->saveImage($previewTmp)
                    ->resizeImage(PHOTO_BOL_PhotoService::DIM_SMALL_WIDTH, PHOTO_BOL_PhotoService::DIM_SMALL_HEIGHT, TRUE)
                    ->saveImage($smallTmp)
                    ->destroy();
            
            $this->photoTemporaryDao->save($tmpPhoto);
             */
            
            $smallTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 4);
            $smallImg = new UTIL_Image($source);
            $smallImg->resizeImage(PHOTO_BOL_PhotoService::DIM_SMALL_WIDTH, PHOTO_BOL_PhotoService::DIM_SMALL_HEIGHT, TRUE)
                ->orientateImage()
                ->saveImage($smallTmp)
                ->destroy();
            
            
            $previewTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 1);
            $previewImage = new UTIL_Image($source);
            $previewImage->resizeImage(PHOTO_BOL_PhotoService::DIM_PREVIEW_WIDTH, PHOTO_BOL_PhotoService::DIM_PREVIEW_HEIGHT)
                ->orientateImage()
                ->saveImage($previewTmp)
                ->destroy();
            
            $mainTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 2);
            $main = new UTIL_Image($source);
            $mainImage = $main->resizeImage(PHOTO_BOL_PhotoService::DIM_MAIN_WIDTH, PHOTO_BOL_PhotoService::DIM_MAIN_HEIGHT)
                ->orientateImage()
                ->saveImage($mainTmp);
            $mainImageResized = $mainImage->imageResized();
            $mainImage->destroy();
            
            $originalTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 3);
            $originalImage = new UTIL_Image($source);
            $originalImage->resizeImage(PHOTO_BOL_PhotoService::DIM_ORIGINAL_WIDTH, PHOTO_BOL_PhotoService::DIM_ORIGINAL_HEIGHT)
                ->orientateImage()
                ->saveImage($originalTmp)
                ->destroy();
            
            if ( $mainImageResized && (bool)PEEP::getConfig()->getValue('photo', 'store_fullsize') )
            {
                $fullscreenTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmpPhoto->id, 5);
                $fullscreen = new UTIL_Image($source);
                $fullscreen->resizeImage(PHOTO_BOL_PhotoService::DIM_FULLSCREEN_WIDTH, PHOTO_BOL_PhotoService::DIM_FULLSCREEN_HEIGHT)
                    ->orientateImage()
                    ->saveImage($fullscreenTmp)
                    ->destroy();
                
                $tmpPhoto->hasFullsize = 1;
            }
            
            $this->photoTemporaryDao->save($tmpPhoto);
        }
        catch ( WideImage_Exception $e )
        {
            $this->photoTemporaryDao->deleteById($tmpPhoto->id);
            
            return FALSE;
        }
        
        return $tmpPhoto->id;
    }
    
    public function moveTemporaryPhoto( $tmpId, $albumId, $desc, $tag = NULL, $angle = 0, $uploadKey = null, $status = null )
    {
        $tmp = $this->photoTemporaryDao->findById($tmpId);
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId);
        
        if ( !$tmp || !$album )
        {
            return FALSE;
        }
        
        $previewTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmp->id, 1);
        $mainTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmp->id, 2);
        $originalTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmp->id, 3);
        $smallTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmp->id, 4);
        $fullscreenTmp = $this->photoTemporaryDao->getTemporaryPhotoPath($tmp->id, 5);
        
        $privacy = PEEP::getEventManager()->call('plugin.privacy.get_privacy', 
            array('ownerId' => $album->userId, 'action' => 'photo_view_album')
        );
        
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        
        $photo = new PHOTO_BOL_Photo();
        $photo->description = htmlspecialchars(trim($desc));
        $photo->albumId = $albumId;
        $photo->addDatetime = time();
        $photo->status = empty($status) ? "approved" : $status;
        $photo->hasFullsize = (int)$tmp->hasFullsize;
        $photo->privacy = !empty($privacy) ? $privacy : 'everybody';
        $photo->hash = uniqid();
        $photo->uploadKey = empty($uploadKey) ? $photoService->getPhotoUploadKey($albumId) : $uploadKey;
        PHOTO_BOL_PhotoDao::getInstance()->save($photo);
        
        try
        {
            $storage = PEEP::getStorage();
            $dimension = array();
            
            if ( (int)$angle !== 0 )
            {
                $tmpImage = $tmp->hasFullsize ? ((bool)PEEP::getConfig()->getValue('photo', 'store_fullsize') ? $originalTmp : $fullscreenTmp) : $mainTmp;
                
                $smallImg = new UTIL_Image($tmpImage);
                $smallImg->resizeImage(PHOTO_BOL_PhotoService::DIM_SMALL_WIDTH, PHOTO_BOL_PhotoService::DIM_SMALL_HEIGHT, TRUE)
                    ->rotate($angle)
                    ->saveImage($smallTmp);
                $storage->copyFile($smallTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_SMALL)
                );
                $dimension[PHOTO_BOL_PhotoService::TYPE_SMALL] = array($smallImg->getWidth(), $smallImg->getHeight());
                $smallImg->destroy();
            
                $previewImage = new UTIL_Image($tmpImage);
                $previewImage->resizeImage(PHOTO_BOL_PhotoService::DIM_PREVIEW_WIDTH, PHOTO_BOL_PhotoService::DIM_PREVIEW_HEIGHT)
                    ->rotate($angle)
                    ->saveImage($previewTmp);
                $storage->copyFile($previewTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_PREVIEW)
                );
                $dimension[PHOTO_BOL_PhotoService::TYPE_PREVIEW] = array($previewImage->getWidth(), $previewImage->getHeight());
                $previewImage->destroy();
                
                $main = new UTIL_Image($tmpImage);
                $main->resizeImage(PHOTO_BOL_PhotoService::DIM_MAIN_WIDTH, PHOTO_BOL_PhotoService::DIM_MAIN_HEIGHT)
                    ->rotate($angle)
                    ->saveImage($mainTmp);
                $storage->copyFile($mainTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_MAIN)
                );
                $dimension[PHOTO_BOL_PhotoService::TYPE_MAIN] = array($main->getWidth(), $main->getHeight());
                $main->destroy();
                
                $originalImage = new UTIL_Image($tmpImage);
                $originalImage->resizeImage(PHOTO_BOL_PhotoService::DIM_ORIGINAL_WIDTH, PHOTO_BOL_PhotoService::DIM_ORIGINAL_HEIGHT)
                    ->rotate($angle)
                    ->saveImage($originalTmp);
                $storage->copyFile($originalTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_ORIGINAL)
                );
                $dimension[PHOTO_BOL_PhotoService::TYPE_ORIGINAL] = array($originalImage->getWidth(), $originalImage->getHeight());
                $originalImage->destroy();
                    
                if ( $tmp->hasFullsize && (bool)PEEP::getConfig()->getValue('photo', 'store_fullsize') )
                {
                    $fullscreen = new UTIL_Image($tmpImage);
                    $fullscreen->resizeImage(PHOTO_BOL_PhotoService::DIM_FULLSCREEN_WIDTH, PHOTO_BOL_PhotoService::DIM_FULLSCREEN_HEIGHT)
                        ->rotate($angle)
                        ->saveImage($fullscreenTmp);
                    $storage->copyFile($fullscreenTmp,
                        $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_FULLSCREEN)
                    );
                    $dimension[PHOTO_BOL_PhotoService::TYPE_FULLSCREEN] = array($fullscreen->getWidth(), $fullscreen->getHeight());
                    $fullscreen->destroy();
                }
            }
            else
            {
                $storage->copyFile($smallTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_SMALL)
                );
                list($width, $height) = getimagesize($smallTmp);
                $dimension[PHOTO_BOL_PhotoService::TYPE_SMALL] = array($width, $height);
                
                $storage->copyFile($previewTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_PREVIEW)
                );
                list($width, $height) = getimagesize($previewTmp);
                $dimension[PHOTO_BOL_PhotoService::TYPE_PREVIEW] = array($width, $height);
                
                $storage->copyFile($mainTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_MAIN)
                );
                list($width, $height) = getimagesize($mainTmp);
                $dimension[PHOTO_BOL_PhotoService::TYPE_MAIN] = array($width, $height);
                
                $storage->copyFile($originalTmp,
                    $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_ORIGINAL)
                );
                list($width, $height) = getimagesize($originalTmp);
                $dimension[PHOTO_BOL_PhotoService::TYPE_ORIGINAL] = array($width, $height);
                
                if ( $tmp->hasFullsize && (bool)PEEP::getConfig()->getValue('photo', 'store_fullsize') )
                {
                    $storage->copyFile($fullscreenTmp,
                        $photoService->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_FULLSCREEN)
                    );
                    list($width, $height) = getimagesize($fullscreenTmp);
                    $dimension[PHOTO_BOL_PhotoService::TYPE_FULLSCREEN] = array($width, $height);
                }
            }
            
            $photo->setDimension(json_encode($dimension));
            PHOTO_BOL_PhotoDao::getInstance()->save($photo);

            if ( mb_strlen($desc) )
            {
                BOL_TagService::getInstance()->updateEntityTags($photo->id, 'photo', $photoService->descToHashtag($desc));
            }
            
            if ( mb_strlen($tag) )
            {
                BOL_TagService::getInstance()->updateEntityTags($photo->id, 'photo', explode(',', $tag));
            }

            PEEP::getEventManager()->trigger(new PEEP_Event('photo.onMoveTemporaryPhoto', array('tmpId' => $tmpId, 'albumId' => $albumId, 'photoId' => $photo->id)));
        }
        catch ( Exception $e )
        {
            $photo = NULL;
        }

        return $photo;
    }
    
    public function findUserTemporaryPhotos( $userId, $orderBy = 'timestamp' )
    {
        $list = $this->photoTemporaryDao->findByUserId($userId, $orderBy);
        
        $result = array();
        if ( $list )
        {
            foreach ( $list as $photo )
            {
                $result[$photo->id]['dto'] = $photo;
                $result[$photo->id]['imageSrc'] = $this->photoTemporaryDao->getTemporaryPhotoUrl($photo->id, 1);
            }
        }
        
        return $result;
    }
    
    public function deleteUserTemporaryPhotos( $userId )
    {
        $list = $this->photoTemporaryDao->findByUserId($userId);
        
        if ( !$list )
        {
            return true;
        }
        
        foreach ( $list as $photo )
        {
            @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photo->id, 1));
            @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photo->id, 2));
            @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photo->id, 3));
            @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photo->id, 4));
            @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photo->id, 5));
            
            $this->photoTemporaryDao->delete($photo);
        }
        
        return true;
    }
    
    public function deleteTemporaryPhoto( $photoId )
    {
        /** @var PHOTO_BOL_Photo $photo */
        $photo = $this->photoTemporaryDao->findById($photoId);
        if ( !$photo )
        {
            return false;
        }
        
        @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photoId, 1));
        @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photoId, 2));
        @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photoId, 3));
        @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photoId, 4));
        @unlink($this->photoTemporaryDao->getTemporaryPhotoPath($photoId, 5));
        
        $this->photoTemporaryDao->delete($photo);
        
        return true;
    }
    
    public function deleteLimitedPhotos()
    {   
        foreach ( $this->photoTemporaryDao->findLimitedPhotos(self::TEMPORARY_PHOTO_LIVE_LIMIT) as $id )
        {
            $this->deleteTemporaryPhoto($id);
        }
    }
}