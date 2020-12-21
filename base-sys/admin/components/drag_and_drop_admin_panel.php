<?php
/* Peepmatches Light By Peepdev co */

class ADMIN_CMP_DragAndDropAdminPanel extends BASE_CMP_DragAndDropPanel
{

    public function __construct( $placeName, array $componentList, $template = 'drag_and_drop_panel' )
    {
        parent::__construct($placeName, $componentList, $template);

        $customizeAllowed = BOL_ComponentAdminService::getInstance()->findPlace($placeName)->editableByUser;
        $this->assign('customizeAllowed', $customizeAllowed);

        $this->assign('placeName', $placeName);
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $sharedData = array(
            'additionalSettings' => $this->additionalSettingList,
            'place' => $this->placeName
        );
        
        $this->initializeJs('BASE_CTRL_AjaxComponentAdminPanel', 'PEEP_Components_DragAndDrop', $sharedData);
        
        $jsDragAndDropUrl = PEEP::getPluginManager()->getPlugin('ADMIN')->getStaticJsUrl() . 'drag_and_drop.js';
        PEEP::getDocument()->addScript($jsDragAndDropUrl);
    }
}