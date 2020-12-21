<?php

class PHOTO_CLASS_AlbumNameValidator extends PEEP_Validator
{
    private $checkDuplicate;
    private $userId;
    private $albumName;

    public function __construct( $checkDuplicate = TRUE, $userId = NULL, $albumName = NULL )
    {
        $this->errorMessage = PEEP::getLanguage()->text('photo', 'cnews_album_error_msg');
        $this->checkDuplicate = $checkDuplicate;
        $this->albumName = $albumName;
        
        if ( $userId !== NULL )
        {
            $this->userId = (int)$userId;
        }
        else
        {
            $this->userId = PEEP::getUser()->getId();
        }
    }

    public function isValid( $albumName )
    {
        if ( strcasecmp(trim($this->albumName), PEEP::getLanguage()->text('photo', 'cnews_album')) === 0 )
        {
            return TRUE;
        }
        
        if ( strcasecmp(trim($albumName), PEEP::getLanguage()->text('photo', 'cnews_album')) === 0 )
        {
            return FALSE;
        }
        elseif ( $this->checkDuplicate && strcasecmp($albumName, $this->albumName) !== 0 && PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumByName($albumName, $this->userId) !== NULL )
        {
            $this->setErrorMessage(PEEP::getLanguage()->text('photo', 'album_name_error'));
            
            return FALSE;
        }
        
        return TRUE;
    }

    public function getJsValidator()
    {
        return UTIL_JsGenerator::composeJsString('{
            validate : function( value )
            {
                if ( {$albumName} && {$albumName}.trim().toLowerCase() == {$cnewsAlbum}.toString().trim().toLowerCase() )
                {
                    return true;
                }
                    
                if ( value.toString().trim().toLowerCase() == {$cnewsAlbum}.toString().trim().toLowerCase() )
                {
                    throw {$errorMsg};
                }
            }
        }', array(
            'albumName' => $this->albumName,
            'cnewsAlbum' => PEEP::getLanguage()->text('photo', 'cnews_album'),
            'errorMsg' => $this->errorMessage
        ));
    }
}
