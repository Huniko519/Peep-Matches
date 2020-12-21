<?php

class BASE_CMP_Flag extends PEEP_Component
{

    public function __construct( $entityType, $entityId )
    {
        parent::__construct();

        $this->addForm(new FlagForm($entityType, $entityId));
    }
}

class FlagForm extends Form
{

    public function __construct( $entityType, $entityId )
    {
        parent::__construct('flag');

        $this->setAjax(true);

        $this->setAction(PEEP::getRouter()->urlFor('BASE_CTRL_Flag', 'flag'));

        $element = new HiddenField('entityType');
        $element->setValue($entityType);
        $this->addElement($element);
        
        $element = new HiddenField('entityId');
        $element->setValue($entityId);
        $this->addElement($element);
        

        $element = new RadioField('reason');
        $element->setOptions(array(
            'spam' => PEEP::getLanguage()->text('base', 'flag_spam'),
            'offence' => PEEP::getLanguage()->text('base', 'flag_offence'),
            'illegal' => PEEP::getLanguage()->text('base', 'flag_illegal'))
        );

        $flagDto = BOL_FlagService::getInstance()->findFlag($entityType, $entityId, PEEP::getUser()->getId());
        
        if ( $flagDto !== null )
        {
            $element->setValue($flagDto->reason);
        }

        $this->addElement($element);

        PEEP::getDocument()->addOnloadScript(
            "peepForms['{$this->getName()}'].bind('success', function(json){
                if (json['result'] == 'success') {
                    _scope.floatBox && _scope.floatBox.close();
                    PEEP.addScript(json.js);
                }
            })");
    }
}