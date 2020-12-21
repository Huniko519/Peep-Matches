<?php

class CNEWS_FORMAT_ImageContent extends CNEWS_CLASS_Format
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "image" => null,
            "thumbnail" => null,
            "iconClass" => null,
            "title" => '',
            "description" => '',
            "status" => null,
            "url" => null,
            "userList" => null
        );

        $tplVars = array_merge($defaults, $this->vars);
        $tplVars["url"] = $this->getUrl($tplVars["url"]);
        
        if ( !empty($tplVars["userList"]) )
        {
            $tplVars["userList"] = $this->getUserList($tplVars["userList"]);
        }
        
        $this->assign('vars', $tplVars);
    }
    
    protected function getUserList( $data )
    {
        $userList = PEEP::getClassInstance("BASE_CMP_MiniAvatarUserList", $data["ids"]);
        $userList->setEmptyListNoRender(true);
        
        if ( !empty($data["viewAllUrl"]) )
        {
            $userList->setViewMoreUrl($this->getUrl($data["viewAllUrl"]));
        }
        
        return array(
            "label" => $this->getLocalizedText($data['label']),
            "list" => $userList->render()
        );
    }
}
