<?php

class PHOTO_CLASS_AlbumOwnerValidator extends PEEP_Validator
{
    public function isValid( $albumId )
    {
        return !empty($albumId) && (PHOTO_BOL_PhotoAlbumService::getInstance()->isAlbumOwner($albumId, PEEP::getUser()->getId()) || PEEP::getUser()->isAuthorized('photo'));
    }
}
