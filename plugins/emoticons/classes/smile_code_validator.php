<?php



class EMOTICONS_CLASS_SmileCodeValidator extends PEEP_Validator
{
    private $code;
    
    public function __construct( $code = '' )
    {
        $this->code = $code;
        $this->errorMessage = PEEP::getLanguage()->text('emoticons', 'error_msg_code_busy');
    }

    public function isValid( $value )
    {
        if ( strcasecmp($value, $this->code) === 0 )
        {
            return TRUE;
        }
        
        return !EMOTICONS_BOL_Service::getInstance()->isSmileCodeBusy($value);
    }
    
    public function getJsValidator()
    {
        $emoticons = EMOTICONS_BOL_Service::getInstance()->getAllEmoticons();
        $codes = array();
        
        foreach ( $emoticons as $smile )
        {
            $codes[] = strtolower($smile->code);
        }
        
        return UTIL_JsGenerator::composeJsString('{
                validate : function( value )
                {
                    if ( value.toLowerCase() === {$code}.toLowerCase() )
                    {
                        return true;
                    }

                    if ( {$codes}.indexOf(value.toLowerCase()) !== -1 )
                    {
                        throw ' . json_encode($this->getError()) . '
                    }
                },
                getErrorMessage : function(){ return ' . json_encode($this->getError()) . ' }
            }', array(
            'code' => $this->code,
            'codes' => $codes
        ));
    }
}
