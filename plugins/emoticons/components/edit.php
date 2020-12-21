<?php


class EMOTICONS_CMP_Edit extends PEEP_Component
{
    public function __construct( $smileId )
    {
        parent::__construct();
        
        $service = EMOTICONS_BOL_Service::getInstance();
        
        if ( empty($smileId) || ($smile = $service->findSmileById($smileId)) === NULL )
        {
            $this->setVisible(FALSE);
            
            return;
        }
        
        $this->addForm(new EMOTICONS_CLASS_EditForm($smile->id, $smile->code));
        
        $this->assign('smileUrl', $service->getEmoticonsUrl());
        $this->assign('smileName', $smile->name);
        $this->assign('smileCode', $smile->code);
    }
}
