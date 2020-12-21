<?php

class USERCREDITS_CLASS_SetCreditsForm extends Form
{
    public function __construct()
    {
        parent::__construct('set-credits-form');

        $this->setAjax(true);
        $this->setAction(PEEP::getRouter()->urlFor('USERCREDITS_CTRL_Ajax', 'setCredits'));

        $lang = PEEP::getLanguage();

        $userIdField = new HiddenField('userId');
        $userIdField->setRequired(true);
        $this->addElement($userIdField);

        $balance = new TextField('balance');
        $this->addElement($balance);

        $submit = new Submit('save');
        $submit->setValue($lang->text('base', 'edit_button'));
        $this->addElement($submit);

        $js = 'peepForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error ){
                PEEP.error(data.error);
            }
            
            if ( data.message ) {
                PEEP.info(data.message);
            }

            _scope.floatBox && _scope.floatBox.close();
            _scope.callBack && _scope.callBack(data);
        });';

        PEEP::getDocument()->addOnloadScript($js);
    }
}