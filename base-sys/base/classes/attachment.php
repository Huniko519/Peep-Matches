<?php

class BASE_CLASS_Attachment extends PEEP_Component
{
    private $uid;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $pluginKey, $uid, $buttonId )
    {
        parent::__construct();
        $language = PEEP::getLanguage();
        $this->uid = $uid;
        $previewContId = 'attch_preview_' . $this->uid;

        $params = array(
            'uid' => $uid,
            'previewId' => $previewContId,
            'buttonId' => $buttonId,
            'pluginKey' => $pluginKey,
            'addPhotoUrl' => PEEP::getRouter()->urlFor('BASE_CTRL_Attachment', 'addPhoto'),
            'langs' => array(
                'attchLabel' => $language->text('base', 'attch_attachment_label')
            )
        );

        $this->assign('previewId', $previewContId);
        PEEP::getDocument()->addScript(PEEP::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
        PEEP::getDocument()->addOnloadScript("window.peepPhotoAttachment['" . $uid . "'] =  new PEEPPhotoAttachment(" . json_encode($params) . ");");
    }
}
