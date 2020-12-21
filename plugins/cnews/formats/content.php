<?php

class CNEWS_FORMAT_Content extends CNEWS_CLASS_Format
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "iconClass" => null,
            "title" => '',
            "description" => '',
            "status" => null,
            "url" => null,
            'activity' => null
        );

        $tplVars = array_merge($defaults, $this->vars);

        if ( !empty($tplVars['activity']['title']) )
        {
            $tplVars['activity']["title"] = $this->getLocalizedText($tplVars['activity']["title"]);
        }

        $tplVars["url"] = $this->getUrl($tplVars["url"]);
        $tplVars["title"] = $this->getLocalizedText($tplVars["title"]);
        
        $this->assign('vars', $tplVars);
    }
}
