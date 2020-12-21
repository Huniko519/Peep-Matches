<?php

class USERCREDITS_CLASS_GrantCreditsForm extends Form
{
    public function __construct()
    {
        parent::__construct('grant-credits-form');

        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(PEEP::getRouter()->urlFor('USERCREDITS_CTRL_Ajax', 'grantCredits'));

        $lang = PEEP::getLanguage();

        $userIdField = new HiddenField('userId');
        $userIdField->setRequired(true);
        $this->addElement($userIdField);

        $amount = new TextField('amount');
        $amount->setRequired(true);
        $this->addElement($amount);

        $submit = new Submit('grant');
        $submit->setValue($lang->text('usercredits', 'grant'));
        $this->addElement($submit);

        $js = 'peepForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error ) {
                PEEP.error(data.error);
                return;
            }

            if ( data.message ) {
                PEEP.info(data.message);
                _scope.floatBox && _scope.floatBox.close();
                
                if ( data.credits == "0" ) {
                    window.setTimeout(function(){
                        document.location.reload();
                    }, 600);

                    return;
                }
            }
        });';

        PEEP::getDocument()->addOnloadScript($js);
    }
}