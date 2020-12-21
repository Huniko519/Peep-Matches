<?php

class EMOTICONS_CLASS_EditForm extends Form
{
    CONST FORM_NAME = 'smile-edit';
    CONST ELEMENT_SMILE_ID = 'smile-id';
    CONST ELEMENT_SMILE_CODE = 'smile-code';
    CONST ELEMENT_SUBMIT = 'save';
    
    public function __construct( $smileId, $smileCode )
    {
        parent::__construct(self::FORM_NAME);
        
        $this->setAction(PEEP::getRouter()->urlForRoute('emoticons.admin_edit'));

        $id = new HiddenField(self::ELEMENT_SMILE_ID);
        $id->setRequired();
        $id->setValue($smileId);
        $this->addElement($id);

        $code = new TextField(self::ELEMENT_SMILE_CODE);
        $code->setRequired();
        $code->addValidator(new EMOTICONS_CLASS_SmileCodeValidator($smileCode));
        $code->setValue($smileCode);
        $code->setLabel(PEEP::getLanguage()->text('emoticons', 'edit_code_label'));
        $code->setDescription(PEEP::getLanguage()->text('emoticons', 'prohibited_chars_desc', array(
            'prohibited' => implode(',', EMOTICONS_BOL_Service::getInstance()->getProhibitedChars()),
            'replacer' => EMOTICONS_BOL_Service::PROHIBIT_CHAR_REPLACER
        )));
        $this->addElement($code);

        $submit = new Submit(self::ELEMENT_SUBMIT);
        $submit->setValue(PEEP::getLanguage()->text('emoticons', 'smile_edit_save'));
        $this->addElement($submit);
    }
}
