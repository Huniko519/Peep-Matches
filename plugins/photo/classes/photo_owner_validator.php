<?php

class PHOTO_CLASS_PhotoOwnerValidator extends PEEP_Validator
{
    public function __construct()
    {
        $this->errorMessage = PEEP::getLanguage()->text('photo', 'no_photo_found');
    }

    public function isValid( $photoId )
    {
        return !empty($photoId) &&
            ($photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId)) !== NULL &&
            ($album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId)) !== NULL &&
            ($album->userId == PEEP::getUser()->getId() || PEEP::getUser()->isAuthorized('photo'));
    }
}
