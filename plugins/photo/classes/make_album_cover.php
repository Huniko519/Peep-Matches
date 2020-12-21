<?php

class PHOTO_CLASS_MakeAlbumCover extends Form
{
    public function __construct()
    {
        parent::__construct('album-cover-maker');
        
        $this->setAjax(TRUE);
        $this->setAction(PEEP::getRouter()->urlForRoute('photo.ajax_album_cover'));
        $this->setAjaxResetOnSuccess(TRUE);

        $coords = new HiddenField('coords');
        $this->addElement($coords);

        $albumIdField = new HiddenField('albumId');
        $albumIdField->setRequired();
        $albumIdField->addValidator(new PHOTO_CLASS_AlbumOwnerValidator());
        $this->addElement($albumIdField);

        $photoIdField = new HiddenField('photoId');
        $this->addElement($photoIdField);

        $submit = new Submit('save');
        $submit->setValue(PEEP::getLanguage()->text('photo', 'btn_edit'));
        $this->addElement($submit);
    }
}
