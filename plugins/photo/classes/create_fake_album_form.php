<?php

class PHOTO_CLASS_CreateFakeAlbumForm extends PHOTO_CLASS_AbstractPhotoForm
{
    const FORM_NAME = 'create_fake_album';
    const ELEMENT_ALBUM_NAME = 'album_name';
    const ELEMENT_ALBUM_DESC = 'album_desc';
    const SUBMIT_SUBMIT = 'submit';

    public function __construct()
    {
        parent::__construct(self::FORM_NAME);

        $language = PEEP::getLanguage();

        $this->setAjax();
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(PEEP::getRouter()->urlFor('PHOTO_CTRL_AjaxUpload', 'checkFakeAlbumData'));
        $this->bindJsFunction(self::BIND_SUCCESS, UTIL_JsGenerator::composeJsString('function( data )
        {
            if ( !data.result )
            {
                var form = peepForms[this.name];

                Object.keys(data.errors).forEach(function( item )
                {
                    var arr = data.errors[item];

                    if ( arr.length !== 0 )
                    {
                        form.getElement(item).showError(arr.shift());
                    }
                });

                return;
            }

            if ( PEEP.getActiveFloatBox() ) PEEP.getActiveFloatBox().close();

            var formData = data.data;
            var params = {
                albumId: 0,
                albumName: formData[{$album_name}],
                albumDescription: formData[{$album_desc}],
                url: "",
                data: formData
            };
            var ajaxUploadPhotoFB = PEEP.ajaxFloatBox("PHOTO_CMP_AjaxUpload", params, {
                title: {$title},
                width: "746px",
                onLoad: function()
                {
                    PEEP.trigger("photo.ready_fake_album", [formData]);
                }
            });

            ajaxUploadPhotoFB.bind("close", function()
            {
                if ( ajaxPhotoUploader.isHasData() )
                {
                    if ( confirm({$confirm}) )
                    {
                        PEEP.trigger("photo.onCloseUploaderFloatBox");

                        return true;
                    }

                    return false;
                }
                else
                {
                    PEEP.trigger("photo.onCloseUploaderFloatBox");
                }
            });
        }', array(
            'album_name' => self::ELEMENT_ALBUM_NAME,
            'album_desc' => self::ELEMENT_ALBUM_DESC,
            'title' => $language->text('photo', 'upload_photos'),
            'confirm' => $language->text('photo', 'close_alert')
        )));

        $albumNameInput = new TextField(self::ELEMENT_ALBUM_NAME);
        $albumNameInput->setRequired();
        $albumNameInput->addValidator(new PHOTO_CLASS_AlbumNameValidator(false));
        $albumNameInput->setHasInvitation(true);
        $albumNameInput->setInvitation($language->text('photo', 'album_name'));
        $this->addElement($albumNameInput);

        $albumDescInput = new Textarea(self::ELEMENT_ALBUM_DESC);
        $albumDescInput->setHasInvitation(true);
        $albumDescInput->setInvitation($language->text('photo', 'album_desc'));
        $this->addElement($albumDescInput);

        $submit = new Submit(self::SUBMIT_SUBMIT);
        $submit->setValue($language->text('photo', 'add_photos'));
        $this->addElement($submit);

        $this->triggerReady();
    }

    public function getOwnElements()
    {
        return array(
            self::ELEMENT_ALBUM_NAME,
            self::ELEMENT_ALBUM_DESC,
            self::SUBMIT_SUBMIT
        );
    }
}
