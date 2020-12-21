<?php

class UTIL_Validator
{
    const PASSWORD_MIN_LENGTH = 4;

    const PASSWORD_MAX_LENGTH = 15;

    const USER_NAME_PATTERN = '/^[\w]{1,32}$/';

    const EMAIL_PATTERN = '/^([\w\-\.\+\%]*[\w])@((?:[A-Za-z0-9\-]+\.)+[A-Za-z]{2,})$/';

    const URL_PATTERN = '/^(http(s)?:\/\/)?((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-z,A-Z][\w-]*)))(:[1-9][0-9]*)?(\/?([\w-.\,\/:%+@&*=~]+[\w- \,.\/?:%+@&=*|]*)?)?(#(.*))?$/';

    const INT_PATTERN = '/^[-+]?[0-9]+$/';

    const FLOAT_PATTERN = '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/';

    const ALPHA_NUMERIC_PATTERN = '/^[A-Za-z0-9]+$/';

    public static function isEmailValid( $value )
    {
        $pattern = self::EMAIL_PATTERN;

        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isUrlValid( $value )
    {
        $pattern = self::URL_PATTERN;

        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isIntValid( $value )
    {
        $intValue = (int) $value;

        if ( !preg_match(self::INT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isFloatValid( $value )
    {
        $floatValue = (float) $value;

        if ( !preg_match(self::FLOAT_PATTERN, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isAlphaNumericValid( $value )
    {
        $pattern = self::ALPHA_NUMERIC_PATTERN;

        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isUserNameValid( $value )
    {
        $pattern = self::USER_NAME_PATTERN;
        $trimValue = trim($value);

        if ( !preg_match($pattern, $value) )
        {
            return false;
        }

        return true;
    }

    public static function isDateValid( $month, $day, $year )
    {
        if ( !checkdate($month, $day, $year) )
        {
            return false;
        }

        return true;
    }

    public static function isCaptchaValid( $value )
    {
        if ( $value === null )
        {
            return false;
        }

        require_once PEEP_DIR_LIB . 'securimage/securimage.php';
        $img = new Securimage();

        if ( !$img->check($value) )
        {
            return false;
        }

        return true;
    }
}