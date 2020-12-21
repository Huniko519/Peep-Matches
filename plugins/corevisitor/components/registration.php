<?php

class COREVISITOR_CMP_Registration extends PEEP_Component
{
    public function __construct() {

        parent::__construct();
if (PEEP::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
        }
        $sAddress = $_SERVER['HTTP_HOST'];
        $form = new COREVISITOR_CLASS_RegistrationForm("joinForm");
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $form->setAction('createaccount');
        $this->addForm($form);

        $submitedFields = array();

        if ( PEEP::getRequest()->isPost() && $form->isValid($_POST) ) {
            $values = $form->getValues();


            foreach ( $form->getElements() as $element ) {
                /* @var $element FormElement */
                if ($element->getName() == 'file') {
                    $submitedFields[] = array(
                        "label" => $element->getName(),
                        "value" => $_FILES['file']['name'].' ('.$_FILES['file']['size'].' bytes)'
                    );
                }
                else {
                    $submitedFields[] = array(
                        "label" => $element->getName(),
                        "value" => is_array($values[$element->getName()]) ? implode(", ", $values[$element->getName()]) : $values[$element->getName()]
                    );
                }
            }
            $this->assign("submitedFields", $submitedFields);
        }
    }

}
