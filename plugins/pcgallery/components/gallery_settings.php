<?php


class PCGALLERY_CMP_GallerySettings extends PEEP_Component
{
    public function __construct( $userId ) 
    {
        parent::__construct();
        
        $data = PEEP::getEventManager()->call("photo.entity_albums_find", array(
            "entityType" => "user",
            "entityId" => $userId
        ));
        
        $albums = empty($data["albums"]) ? array() : $data["albums"];
        
        $source = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_source", $userId);
        $this->assign("source", $source == "album" ? "album": "all");
        
        $selectedAlbum = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_album", $userId);
        
        $form = new Form("pcGallerySettings");
        $form->setEmptyElementsErrorMessage(null);
        $form->setAction(PEEP::getRouter()->urlFor("PCGALLERY_CTRL_Gallery", "saveSettings"));
        
        $element = new HiddenField("userId");
        $element->setValue($userId);
        $form->addElement($element);
        
        $element = new Selectbox("album");
        $element->setHasInvitation(true);
        $element->setInvitation(PEEP::getLanguage()->text("pcgallery", "settings_album_invitation"));
        
        $validator = new PCGALLERY_AlbumValidator();
        $element->addValidator($validator);
        
        $albumsPhotoCount = array();
        
        foreach ( $albums as $album ) 
        {
            $element->addOption($album["id"], $album["name"] . " ({$album["photoCount"]})");
            $albumsPhotoCount[$album["id"]] = $album["photoCount"];
            
            if ( $album["id"] == $selectedAlbum )
            {
                $element->setValue($album["id"]);
            }
        }
        
        PEEP::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString('window.pcgallery_settingsAlbumCounts = {$albumsCount};', array(
            "albumsCount" => $albumsPhotoCount
        )));
        
        $element->setLabel(PEEP::getLanguage()->text("pcgallery", "source_album_label"));
        
        $form->addElement($element);
        
        $submit = new Submit("save");
        $submit->setValue(PEEP::getLanguage()->text("pcgallery", "save_settings_btn_label"));
        $form->addElement($submit);
        
        $this->addForm($form);
    }
}

class PCGALLERY_AlbumValidator extends PEEP_Validator
{
    public function getError() {
        return PEEP::getLanguage()->text("pcgallery", "settings_album_required");
    }

    public function isValid($value) {
        return true;
    }
    
    public function getJsValidator() {
        return "{
            validate : function( value ){
                if ( $('#ugallery-source-album').get(0).checked ) {
                    if ( !value ) throw " . json_encode($this->getError()) . ";
                    var photoCount = parseInt(window.pcgallery_settingsAlbumCounts[value]);
                    
                    if ( photoCount < 4 ) {
                        throw " . json_encode(PEEP::getLanguage()->text("pcgallery", "settings_album_not_enough_photos")) . ";
                    }
                }
            }
        }";
    }
}