<?php

class PHOTO_CMP_CreateAlbum extends PEEP_Component
{
    public function __construct( $fromAlbum, $photoIdList )
    {
        parent::__construct();
        
        $form = new PHOTO_CLASS_AlbumAddForm();
        $form->getElement('from-album')->setValue($fromAlbum);
        $form->getElement('photos')->setValue($photoIdList);
        $this->addForm($form);
    }
}
