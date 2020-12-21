<?php

class PCGALLERY_CTRL_Gallery extends PEEP_ActionController
{
    public function saveSettings()
    {
        $source = $_POST["source"] == "all" ? "all" : "album";
        $album = $_POST["album"];
        $userId = $_POST["userId"];
        
        if ( $userId != PEEP::getUser()->getId() && !PEEP::getUser()->isAuthorized("pcgallery") )
        {
            throw new Redirect403Exception();
        }
        
        BOL_PreferenceService::getInstance()->savePreferenceValue("pcgallery_album", $album, $userId);
        BOL_PreferenceService::getInstance()->savePreferenceValue("pcgallery_source", $source, $userId);
        
        PEEP::getFeedback()->info(PEEP::getLanguage()->text("pcgallery", "settings_saved_message"));
        $this->redirect(BOL_UserService::getInstance()->getUserUrl($userId));
    }
}
