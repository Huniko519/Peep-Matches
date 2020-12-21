<?php

class PHOTO_CMP_EditPhoto extends PEEP_Component
{
    public function __construct( $photoId )
    {
        parent::__construct();

        if ( ($photo = PHOTO_BOL_PhotoDao::getInstance()->findById($photoId)) === NULL ||
            ($album = PHOTO_BOL_PhotoAlbumDao::getInstance()->findById($photo->albumId)) === null ||
            !($album->userId == PEEP::getUser()->getId() || PEEP::getUser()->isAuthorized('photo')) )
        {
            $this->setVisible(FALSE);
            
            return;
        }
        
        $this->addForm(new PHOTO_CLASS_EditForm($photo->id));
        
        $cnewsAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getCnewsAlbum($album->userId);
        $exclude = array();
        
        if ( !empty($cnewsAlbum) )
        {
            $exclude[] = $cnewsAlbum->id;
        }

        $this->addComponent('albumNameList', PEEP::getClassInstance('PHOTO_CMP_AlbumNameList', PEEP::getUser()->getId(), $exclude));
        $language = PEEP::getLanguage();
        
        PEEP::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString(';var panel = $(document.getElementById("photo_edit_form"));
                var albumList = $(".peep_dropdown_list", panel);
                var albumInput = $("input[name=\'album\']", panel);
                var album = {$album};
                var hideAlbumList = function()
                {
                    albumList.hide();
                    $(".upload_photo_spinner", panel).removeClass("peep_dropdown_arrow_up").addClass("peep_dropdown_arrow_down");
                };
                var showAlbumList = function()
                {
                    albumList.show();
                    $(".upload_photo_spinner", panel).removeClass("peep_dropdown_arrow_down").addClass("peep_dropdown_arrow_up");
                };

                $(".upload_photo_spinner", panel).add(albumInput).on("click", function( event )
                {
                    if ( albumList.is(":visible") )
                    {
                        hideAlbumList();
                    }
                    else
                    {
                        showAlbumList();
                    }

                    event.stopPropagation();
                });

                albumList.find("li").on("click", function()
                {
                    hideAlbumList();
                    peepForms["photo-edit-form"].removeErrors();
                }).eq(0).on("click", function()
                {
                    albumInput.val({$create_album});
                    $(".new-album", panel).show();
                    $("input[name=\'album-name\']", panel).val({$album_name});
                    $("textarea", panel).val({$album_desc});
                }).end().slice(2).on("click", function()
                {
                    albumInput.val($(this).data("name"));
                    $(".new-album", panel).hide();
                    $("input[name=\'album-name\']", panel).val(albumInput.val());
                    $("textarea", panel).val("");
                });

                $(document).on("click", function( event )
                {
                    if ( event.target.id === "ajax-upload-album" )
                    {
                        event.stopPropagation();

                        return false;
                    }

                    hideAlbumList();
                });
                
                PEEP.bind("base.onFormReady.photo-edit-form", function()
                {
                    if ( album.name == {$cnewsAlbumName} )
                    {
                        this.getElement("album-name").validators.length = 0;
                        this.getElement("album-name").addValidator({
                            validate : function( value ){
                            if(  $.isArray(value) ){ if(value.length == 0  ) throw {$required}; return;}
                            else if( !value || $.trim(value).length == 0 ){ throw {$required}; }
                            },
                            getErrorMessage : function(){ return {$required} }
                        });
                        this.bind("submit", function()
                        {
                            
                        });
                    }
                });
                '
            ,
            array(
                'create_album' => $language->text('photo', 'create_album'),
                'album_name' => $language->text('photo', 'album_name'),
                'album_desc' => $language->text('photo', 'album_desc'),
                'album' => get_object_vars($album),
                'cnewsAlbumName' => PEEP::getLanguage()->text('photo', 'cnews_album'),
                'required' => PEEP::getLanguage()->text('base', 'form_validator_required_error_message')
            ))
        );
    }
}
