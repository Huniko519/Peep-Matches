<?php

class BASE_CMP_AjaxOembedAttachment extends PEEP_Component
{
    protected $oembed = array(), $uniqId;

    public function __construct( $oembed )
    {
        parent::__construct();

        $this->uniqId = uniqid('eqattachment');
        $this->assign('uniqId', $this->uniqId);

        $this->oembed = $oembed;
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('PEEP_AttachmentItemColletction', $this->uniqId), 'PEEP_Attachment', array($this->uniqId, $this->oembed));

        PEEP::getDocument()->addOnloadScript($js);

        return $this->uniqId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('data', $this->oembed);
    }
}
