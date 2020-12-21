<?php

class SEARCHSYS_CMP_UserSearch extends PEEP_Component
{
    public function __construct( )
    {
        parent::__construct();

        $searchSystemForm = new SEARCHSYS_CLASS_SearchSystemForm($this);
        $this->addForm($searchSystemForm);

        $config = PEEP::getConfig();

        $this->assign('showAdvanced', $config->getValue('searchsys', 'show_advanced'));
        $this->assign('showSection', $config->getValue('searchsys', 'show_section'));
        $this->assign('onlineOnlyEnabled', $config->getValue('searchsys', 'online_only_enabled'));
        $this->assign('withPhotoEnabled', $config->getValue('searchsys', 'with_photo_enabled'));
        $this->assign('advancedUrl', PEEP::getRouter()->urlForRoute('users-search'));
    }
}