<?php

class EMOTICONS_CLASS_AddForm extends Form
{
    CONST FORM_NAME = 'smile-add';
    CONST ELEMENT_CATEGORY = 'category';
    CONST ELEMENT_SMILE_CODE = 'smile-code';
    CONST ELEMENT_FILE = 'smile-file';
    CONST ELEMENT_SUBMIT = 'save';
    
    public function __construct( $categoryId = NULL )
    {
        parent::__construct(self::FORM_NAME);
        
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->setAction(PEEP::getRouter()->urlForRoute('emoticons.admin_add'));
        
        $hidden = new HiddenField(self::ELEMENT_CATEGORY);
        $hidden->setValue($categoryId);
        $this->addElement($hidden);

        $code = new TextField(self::ELEMENT_SMILE_CODE);
        $code->setRequired();
        $code->addValidator(new EMOTICONS_CLASS_SmileCodeValidator());
        $code->setLabel(PEEP::getLanguage()->text('emoticons', 'edit_code_label'));
        $code->setDescription(PEEP::getLanguage()->text('emoticons', 'prohibited_chars_desc', array(
            'prohibited' => implode(',', EMOTICONS_BOL_Service::getInstance()->getProhibitedChars()),
            'replacer' => EMOTICONS_BOL_Service::PROHIBIT_CHAR_REPLACER
        )));
        $this->addElement($code);
        
        $file = new FileField(self::ELEMENT_FILE);
        $file->addValidator(new EMOTICONS_CLASS_FileValidator(self::ELEMENT_FILE));
        $file->addAttribute('accept', 'image/jpeg,image/png,image/gif');
        $file->setLabel(PEEP::getLanguage()->text('emoticons', 'file_label'));
        $file->setDescription(PEEP::getLanguage()->text('emoticons', 'file_desc'));
        $this->addElement($file);

        $submit = new Submit(self::ELEMENT_SUBMIT);
        $submit->setValue(PEEP::getLanguage()->text('emoticons', 'smile_edit_save'));
        $this->addElement($submit);
    }
}
