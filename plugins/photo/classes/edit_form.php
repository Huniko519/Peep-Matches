<?php

class PHOTO_CLASS_EditForm extends Form
{
    public function __construct( $photoId = NULL )
    {
        parent::__construct('photo-edit-form');
        
        $this->setAjax(TRUE);
        $this->setAction(PEEP::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxUpdatePhoto'));
        $this->bindJsFunction('success', 'function( data )
            {
                PEEP.trigger("photo.afterPhotoEdit", data);
            }');
        
        $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
        
        $photoIdField = new HiddenField('photoId');
        $photoIdField->setRequired(TRUE);
        $photoIdField->setValue($photo->id);
        $photoIdField->addValidator(new PHOTO_CLASS_PhotoOwnerValidator());
        $this->addElement($photoIdField);

        $albumField = new TextField('album');
        $albumField->setId('ajax-upload-album');
        $albumField->setRequired();
        $albumField->setValue($album->name);
        $albumField->setLabel(PEEP::getLanguage()->text('photo', 'create_album'));
        $albumField->addAttribute('class', 'peep_dropdown_btn peep_inputready peep_cursor_pointer');
        $albumField->addAttribute('autocomplete', 'off');
        $albumField->addAttribute('readonly');
        $this->addElement($albumField);
        
        $albumNameField = new TextField('album-name');
        $albumNameField->setRequired();
        $albumNameField->setValue($album->name);
        $albumNameField->addValidator(new PHOTO_CLASS_AlbumNameValidator(FALSE, NULL, $album->name));
        $albumNameField->setHasInvitation(TRUE);
        $albumNameField->setInvitation(PEEP::getLanguage()->text('photo', 'album_name'));
        $albumNameField->addAttribute('class', 'peep_smallmargin invitation');
        $this->addElement($albumNameField);
        
        $desc = new Textarea('description');
        $desc->setHasInvitation(TRUE);
        $desc->setInvitation(PEEP::getLanguage()->text('photo', 'album_desc'));
        $this->addElement($desc);
        
        $photoDesc = new PHOTO_CLASS_HashtagFormElement('photo-desc');
        $photoDesc->setValue($photo->description);
        $photoDesc->setLabel(PEEP::getLanguage()->text('photo', 'album_desc'));
        $this->addElement($photoDesc);

        $submit = new Submit('edit');
        $submit->setValue(PEEP::getLanguage()->text('photo', 'btn_edit'));
        $this->addElement($submit);
    }
}
