<?php

class PHOTO_CLASS_AlbumEditForm extends PHOTO_CLASS_AbstractPhotoForm
{
    const FORM_NAME = 'albumEditForm';
    const ELEMENT_ALBUM_ID = 'album-id';
    const ELEMENT_ALBUM_NAME = 'albumName';
    const ELEMENT_DESC = 'desc';

    public function __construct( $albumId )
    {
        parent::__construct(self::FORM_NAME);
        
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId);
        
        $this->setAction(PEEP::getRouter()->urlForRoute('photo.ajax_update_photo'));
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);

        $albumIdField = new HiddenField(self::ELEMENT_ALBUM_ID);
        $albumIdField->setValue($album->id);
        $albumIdField->setRequired();
        $albumIdField->addValidator(new PHOTO_CLASS_AlbumOwnerValidator());
        $this->addElement($albumIdField);
        
        $albumNameField = new TextField(self::ELEMENT_ALBUM_NAME);
        $albumNameField->setValue($album->name);
        $albumNameField->setRequired();
        
        if ( $album->name != trim(PEEP::getLanguage()->text('photo', 'cnews_album')) )
        {
            $albumNameField->addValidator(new PHOTO_CLASS_AlbumNameValidator(true, null, $album->name));
        }
        
        $albumNameField->addAttribute('class', 'peep_photo_album_name_input');
        $this->addElement($albumNameField);
        
        $desc = new Textarea(self::ELEMENT_DESC);
        $desc->setValue(!empty($album->description) ? $album->description : NULL);
        $desc->setHasInvitation(TRUE);
        $desc->setInvitation(PEEP::getLanguage()->text('photo', 'describe_photo'));
        $desc->addAttribute('class', 'peep_photo_album_description_textarea');
        $this->addElement($desc);

        $this->triggerReady(array(
            'albumId' => $albumId
        ));
    }

    public function getOwnElements()
    {
        return array(
            self::ELEMENT_ALBUM_ID,
            self::ELEMENT_ALBUM_NAME,
            self::ELEMENT_DESC
        );
    }
}
