<?php

abstract class BASE_CLASS_PasswordValidator extends PEEP_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        
    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = PEEP::getLanguage();

        if ( mb_strlen($value) > 0 && mb_strlen($value) < UTIL_Validator::PASSWORD_MIN_LENGTH )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_too_short'));
            return false;
        }
        else if ( mb_strlen($value) > UTIL_Validator::PASSWORD_MAX_LENGTH )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_too_long'));
            return false;
        }
        else if ( isset($_POST['repeatPassword']) && $value !== $_POST['repeatPassword'] )
        {
            $this->setErrorMessage($language->text('base', 'join_error_password_not_valid'));
            return false;
        }

        return true;
    }
}