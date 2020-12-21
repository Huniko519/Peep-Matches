<?php

class BASE_CMP_DragAndDropAdminPanel extends BASE_CMP_DragAndDropPanel
{

    public function __construct( $placeName, array $componentList, $template )
    {
        parent::__construct($placeName, $componentList, $template);

        $jsDragAndDropUrl = PEEP::getPluginManager()->getPlugin('ADMIN')->getStaticJsUrl() . 'drag_and_drop.js';
        PEEP::getDocument()->addScript($jsDragAndDropUrl);

        $customizeAllowed = BOL_ComponentAdminService::getInstance()->findPlace($placeName);
        $this->assign('customizeAllowed', $customizeAllowed);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->initializeJs('BASE_CTRL_AjaxComponentAdminPanel', 'PEEP_Components_DragAndDrop', $this->sharedData);
    }
}