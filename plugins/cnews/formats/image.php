<?php

class CNEWS_FORMAT_Image extends CNEWS_CLASS_Format
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "image" => null,
            "status" => null,
            "url" => null,
            "info" => null
        );
        
        $this->vars = array_merge($defaults, $this->vars);
        $this->vars['url'] = $this->getUrl($this->vars['url']);

        $this->assign('vars', $this->vars);
    }
}