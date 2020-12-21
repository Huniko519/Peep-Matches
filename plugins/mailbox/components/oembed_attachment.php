<?php

class MAILBOX_CMP_OembedAttachment extends PEEP_Component
{
    protected $oembed = array();
    protected $message = "";

    public function __construct($message, $oembed )
    {
        parent::__construct();

        $this->message = $message;
        $this->oembed = $oembed;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if (!empty($this->oembed['title']))
        {
            $this->oembed['title'] = UTIL_String::truncate($this->oembed['title'], 50, '...');
        }

        if (!empty($this->oembed['description']))
        {
            $this->oembed['description'] = UTIL_String::truncate($this->oembed['description'], 60, '...');
        }

        $this->assign('message', $this->message);
        $this->assign('data', $this->oembed);
    }
}
