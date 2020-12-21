<?php

class EMOTICONS_CLASS_FileValidator extends PEEP_Validator
{
    private $fileName;
    
    public function __construct( $fileName )
    {
        $this->fileName = $fileName;
        $this->errorMessage = PEEP::getLanguage()->text('emoticons', 'not_support_file');
    }

    public function isValid( $value )
    {
        return !empty($_FILES[$this->fileName]) && 
            $_FILES[$this->fileName]['error'] === UPLOAD_ERR_OK && 
            in_array($_FILES[$this->fileName]['type'], array('image/jpeg', 'image/png', 'image/gif')) && 
            is_uploaded_file($_FILES[$this->fileName]['tmp_name']);
    }
    
    public function getJsValidator()
    {
        return '{
            validate : function( value )
            {
                if ( ["jpg", "jpeg", "png", "gif"].indexOf(value.split(".").pop()) === -1 )
                {
                    throw ' . json_encode($this->getError()) . '
                }
            },
            getErrorMessage : function(){ return ' . json_encode($this->getError()) . ' }
        }';
    }
}
