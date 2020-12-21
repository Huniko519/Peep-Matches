<?php

class CNEWS_CMP_UpdateStatus extends PEEP_Component
{
    protected $focused = false;
    
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct();

        $form = $this->createForm($feedAutoId, $feedType, $feedId, $actionVisibility);
        $this->addForm($form);
        
        $this->initAttachments($feedAutoId, $form);
$avatarService = BOL_AvatarService::getInstance();
        $userId = PEEP::getUser()->getId();
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);
    }
    
    protected function initAttachments( $feedAutoId, Form $form )
    {
        $attachmentInputId = $form->getElement('attachment')->getId();
        
        $attachmentId = uniqid('nfa-' . $feedAutoId);
        $attachmentBtnId = $attachmentId . "-btn";
        
        $inputId = $form->getElement('status')->getId();
        $js = 'PEEPLinkObserver.observeInput("' . $inputId . '", function(link){
            var ac = $("#attachment_preview_' . $attachmentId . '-oembed");
            if ( ac.data("sleep") ) return;

            ac.show().html("<div class=\"peep_preloader\" style=\"height: 30px;\"></div>");

            this.requestResult(function( r )
            {
                ac.show().html(r);
            });

            this.onResult = function( r )
            {
                $("#' . $attachmentInputId . '").val(JSON.stringify(r));
            };

        });';

        PEEP::getDocument()->addOnloadScript($js);

        $this->assign('uniqId', $attachmentId);

        $attachment = new BASE_CLASS_Attachment("cnews", $attachmentId, $attachmentBtnId);

        $this->addComponent('attachment', $attachment);

        $js = 'var attUid = {$uniqId}, uidUniq = 0; peepForms[{$form}].bind("success", function(data){
                    PEEP.trigger("base.photo_attachment_reset", {pluginKey:"cnews", uid:attUid});
                    peepForms[{$form}].getElement("attachment").setValue("");
                    PEEPLinkObserver.getObserver("' .$inputId. '").resetObserver();
                    $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", false).empty();
                    
                    var attOldUid = attUid;
                    attUid = {$uniqId} + (uidUniq++);
                    PEEP.trigger("base.photo_attachment_uid_update", {
                        uid: attOldUid,
                        newUid: attUid
                    });
                });
                peepForms[{$form}].reset = false;
                
                PEEP.bind("base.add_photo_attachment_submit",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#attachment_preview_" + {$uniqId} + "-oembed").hide().empty();
                            $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", true);
                        }
                    }
                );

                
                PEEP.bind("base.attachment_hide_button_cont",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#" + {$uniqId} + "-btn-cont").hide();
                        }
                    }
                );
                
                PEEP.bind("base.attachment_show_button_cont",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#" + {$uniqId} + "-btn-cont").show();
                        }
                    }
                );

                PEEP.bind("base.attachment_added",
                    function(data){
                        if( data.uid == attUid ) {
                            data.type = "photo";
                            peepForms[{$form}].getElement("attachment").setValue(JSON.stringify(data));
                        }
                    }
                );

                PEEP.bind("base.attachment_deleted",
                    function(data){
                        if( data.uid == attUid ){
                            $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", false).empty();
                            peepForms[{$form}].getElement("attachment").setValue("");
                            PEEPLinkObserver.getObserver("' .$inputId. '").resetObserver();
                        }
                    }
                );';

        $js = UTIL_JsGenerator::composeJsString($js , array(
            'form' => $form->getName(),
            'uniqId' => $attachmentId
        ));

        PEEP::getDocument()->addOnloadScript($js);
    }
    
    /**
     * 
     * @param int $feedAutoId
     * @param string $feedType
     * @param int $feedId
     * @param int $actionVisibility
     * @return Form
     */
    public function createForm( $feedAutoId, $feedType, $feedId, $actionVisibility )
    {
        return new CNEWS_StatusForm($feedAutoId, $feedType, $feedId, $actionVisibility);
    }
    
    public function focusOnInput( $focus = true )
    {
        $this->focused = $focus;
    }
    
    protected function setFocusOnInput()
    {
        $statusId = $this->getForm("cnews_update_status")->getElement("status")->getId();
        PEEP::getDocument()->addOnloadScript('$("#' . $statusId . '").focus();');
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        if ( $this->focused )
        {
            $this->setFocusOnInput();
        }
    }
    
}

class CNEWS_StatusForm extends Form
{
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct('cnews_update_status');

        $this->setAjax();
        $this->setAjaxResetOnSuccess(false);

        $field = new Textarea('status');
        $field->setHasInvitation(true);
        $field->setInvitation( PEEP::getLanguage()->text('cnews', 'status_field_invintation') );
        $this->addElement($field);

        $field = new HiddenField('attachment');
        $this->addElement($field);

        $field = new HiddenField('feedType');
        $field->setValue($feedType);
        $this->addElement($field);

        $field = new HiddenField('feedId');
        $field->setValue($feedId);
        $this->addElement($field);

        $field = new HiddenField('visibility');
        $field->setValue($actionVisibility);
        $this->addElement($field);

        $submit = new Submit('save');
        $submit->setValue(PEEP::getLanguage()->text('cnews', 'status_btn_label'));
        $this->addElement($submit);

        if ( !PEEP::getRequest()->isAjax() )
        {
            $js = UTIL_JsGenerator::composeJsString('
            peepForms["cnews_update_status"].bind( "submit", function( r )
            {
                $(".cnews-status-preloader", "#" + {$autoId}).show();
            });

            peepForms["cnews_update_status"].bind( "success", function( r )
            {
                $(this.status).val("");
                $(".cnews-status-preloader", "#" + {$autoId}).hide();

                if ( r.error ) {
                    PEEP.error(r.error); return;
                }
                
                if ( r.message ) {
                    PEEP.info(r.message);
                }

                if ( r.item )
                {
                    window.peep_cnews_feed_list[{$autoId}].loadNewItem(r.item, false);
                }
            });', array('autoId' => $feedAutoId ));

            PEEP::getDocument()->addOnloadScript( $js );
        }

        $this->setAction( PEEP::getRequest()->buildUrlQueryString(PEEP::getRouter()->urlFor('CNEWS_CTRL_Ajax', 'statusUpdate')) );
    }
}