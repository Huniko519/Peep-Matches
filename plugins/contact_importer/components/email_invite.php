<?php

class CONTACTIMPORTER_CMP_EmailInvite extends PEEP_Component
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $language = PEEP::getLanguage();
        $form = new Form('inite-friends');

        $emailList = new TagsInputField('emailList');
        $emailList->setRequired();
        $emailList->setDelimiterChars(array(',', ' '));
        $emailList->setInvitation($language->text('contactimporter', 'email_field_inv_message'));
        $emailList->setJsRegexp('/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/');
        $form->addElement($emailList);

        $text = new Textarea('text');
        $text->setValue($language->text('contactimporter', 'email_invite_field_default_text'));
        $text->setHasInvitation(true);
        $form->addElement($text);

        $submit = new Submit('submit');
        $form->addElement($submit);

        $form->setAction(PEEP::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Email', 'send'));
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->bindJsFunction(Form::BIND_SUCCESS, "
            function(data){                
                if( data.success ){
                    PEEP.info(data.message);
                    peepForms['inite-friends'].resetForm();
                    window.ciMailFloatBox.close();
                }
                else{
                    PEEP.error(data.message);
                }
              }"
        );
        $this->addForm($form);
    }
}