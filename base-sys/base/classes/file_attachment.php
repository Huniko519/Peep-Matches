<?php

class BASE_CLASS_FileAttachment extends PEEP_Component
{
    private $uid;
    private $inputSelector;
    private $showPreview;
    private $pluginKey;
    private $multiple;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $pluginKey, $uid )
    {
        parent::__construct();
        $this->uid = $uid;
        $this->showPreview = true;
        $this->pluginKey = $pluginKey;
        $this->multiple = true;
    }

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple( $multiple )
    {
        $this->multiple = (bool) $multiple;
    }

    public function getInputSelector()
    {
        return $this->inputSelector;
    }

    public function setInputSelector( $inputSelector )
    {
        $this->inputSelector = trim($inputSelector);
    }

    public function getShowPreview()
    {
        return $this->showPreview;
    }

    public function setShowPreview( $showPreview )
    {
        $this->showPreview = (bool) $showPreview;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = BOL_AttachmentService::getInstance()->getFilesByBundleName($this->pluginKey, $this->uid);
        $itemsArr = array();

        foreach ( $items as $item )
        {
            $itemsArr[] = array('name' => $item['dto']->getOrigFileName(), 'size' => $item['dto']->getSize(), 'dbId' => $item['dto']->getId());
        }

        $params = array(
            'uid' => $this->uid,
            'submitUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Attachment', 'addFile'),
            'deleteUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Attachment', 'deleteFile'),
            'showPreview' => $this->showPreview,
            'selector' => $this->inputSelector,
            'pluginKey' => $this->pluginKey,
            'multiple' => $this->multiple,
            'lItems' => $itemsArr
        );

        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
        PEEP::getDocument()->addOnloadScript("peepFileAttachments['" . $this->uid . "'] = new PEEPFileAttachment(" . json_encode($params) . ");");



        $this->assign('data', array('uid' => $this->uid, 'showPreview' => $this->showPreview, 'selector' => $this->inputSelector));
    }
}
