<?php

class PHOTO_CMP_CreateFakeAlbum extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();

        $form = new PHOTO_CLASS_CreateFakeAlbumForm();
        $this->addForm($form);

        $this->assign('extendInputs', $form->getExtendedElements());
        $this->assign('userId', PEEP::getUser()->getId());
        PEEP::getDocument()->addStyleSheet(PEEP::getPluginManager()->getPlugin('photo')->getStaticCssUrl() . 'photo_upload.css');
    }
}
