<?php

class BASE_CLASS_AvatarFieldValidator extends PEEP_Validator
{
    protected $required = false;

    /**
     * @param bool $required
     */
    public function __construct( $required = false )
    {
        $this->required = $required;

        $language = PEEP::getLanguage();
        $this->setErrorMessage($language->text('base', 'form_validator_required_error_message'));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        if ( !$this->required )
        {
            return true;
        }

        $language = PEEP::getLanguage();
        

        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $path = $avatarService->getTempAvatarPath($key, 3);

        if ( !file_exists($path) )
        {
            return false;
        }

        if ( !is_writable(BOL_AvatarService::getInstance()->getAvatarsDir()) )
        {
            $this->setErrorMessage($language->text('base', 'not_writable_avatar_dir'));

            return false;
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        $condition = '';

        if ( $this->required )
        {
            $condition = "
            if ( value == undefined || $.trim(value).length == 0 ) {
                throw " . json_encode($this->getError()) . ";
            }";
        }

        return "{
                validate : function( value ){ " . $condition . " },
                getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}