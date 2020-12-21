<?php

abstract class PEEP_Validator
{
    /**
     * Error message.
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Checks if provided value is valid.
     *
     * @param mixed $value
     * @return boolean
     */
    abstract function isValid( $value );

    /**
     * Returns validator error message.
     *
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * Sets validator error message.
     *
     * @param string $errorMessage
     * @return PEEP_Validator
     * @throws InvalidArgumentException
     */
    public function setErrorMessage( $errorMessage )
    {
        if ( $errorMessage === null || mb_strlen(trim($errorMessage)) === 0 )
        {
            //throw new InvalidArgumentException('Invalid error message!');
            return;
        }

        $this->errorMessage = trim($errorMessage);
    }

    /**
     * Returns validator js object code.
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
            validate : function( value ){}
        }";
    }
}

class RequiredValidator extends PEEP_Validator
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_required_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @see PEEP_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( is_array($value) )
        {
            if ( sizeof($value) === 0 )
            {
                return false;
            }
        }
        else if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return false;
        }

        return true;
    }

    /**
     * @see PEEP_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                if(  $.isArray(value) ){ if(value.length == 0  ) throw " . json_encode($this->getError()) . "; return;}
                else if( !value || $.trim(value).length == 0 ){ throw " . json_encode($this->getError()) . "; }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

class WyswygRequiredValidator extends PEEP_Validator
{
    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_required_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Required Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    /**
     * @see PEEP_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // process value
        $value = strip_tags(str_replace(array('&nbsp;', '&nbsp'), array(' ', ' '), $value));

        return mb_strlen(trim($value));
    }

    /**
     * @see PEEP_Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
                    // process value
                    value = value.replace(/\&nbsp;|&nbsp/ig,'');
                    value = value.replace(/(<([^>]+)>)/ig,''); 

                    if (!$.trim(value).length) {
                        throw " . json_encode($this->getError()) . ";
                    }
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

class StringValidator extends PEEP_Validator
{
    /**
     * String min length
     *
     * @var int
     */
    private $min;
    /**
     * String max length
     *
     * @var int
     */
    private $max;

    /**
     * Class constructor.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        if ( isset($min) )
        {
            $this->setMinLength($min);
        }

        if ( isset($max) )
        {
            $this->setMaxLength($max);
        }

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_string_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'String Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets string max length
     *
     * @param int $max
     */
    public function setMaxLength( $max )
    {
        if ( !isset($max) )
        {
            throw new InvalidArgumentException('Empty max length!');
        }

        $this->max = (int) $max;
    }

    /**
     * Sets string min length
     *
     * @param int $min
     */
    public function setMinLength( $min )
    {
        if ( !isset($min) )
        {
            throw new InvalidArgumentException('Empty min length!');
        }

        $this->min = (int) $min;
    }

    /**
     * @see PEEP_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( isset($this->min) && mb_strlen($trimValue) < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && mb_strlen($trimValue) > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( $.trim(value).length < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( $.trim(value).length > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

class RegExpValidator extends PEEP_Validator
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * Class constructor.
     *
     * @param string pattern
     */
    public function __construct( $pattern = null )
    {
        if ( isset($pattern) )
        {
            $this->setPattern($pattern);
        }

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_regexp_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Regexp Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }

    /**
     * Sets pattern
     *
     * @param string $pattern
     */
    public function setPattern( $pattern )
    {
        if ( !isset($pattern) || mb_strlen(trim($pattern)) === 0 )
        {
            throw new InvalidArgumentException('Empty pattern!');
        }

        $this->pattern = trim($pattern);
    }

    /**
     * @see PEEP_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $trimValue = trim($value);

        if ( !preg_match($this->pattern, $trimValue) )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        	}}
        ";

        return $js;
    }
}

class EmailValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::EMAIL_PATTERN);

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_email_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Email Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}


class UrlValidator extends RegExpValidator
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::URL_PATTERN);

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Url Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}

class AlphaNumericValidator extends RegExpValidator
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct(UTIL_Validator::ALPHA_NUMERIC_PATTERN);

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_url_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Alphanumeric Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }
}

class IntValidator extends PEEP_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::INT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (int) $min;
        }

        if ( isset($max) )
        {
            $this->max = (int) $max;
        }

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_int_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Int Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $intValue = (int) $value;

        if ( !UTIL_Validator::isIntValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $intValue < (int) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $intValue > (int) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
            	var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseInt(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseInt(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}


class FloatValidator extends PEEP_Validator
{
    /**
     * @var float
     */
    private $min;
    /**
     * @var float
     */
    private $max;
    /**
     * @var string
     */
    private $pattern;

    /**

      /**
     * Class constructor
     *
     * @param float $min
     * @param float $max
     */
    public function __construct( $min = null, $max = null )
    {
        $this->pattern = UTIL_Validator::FLOAT_PATTERN;

        if ( isset($min) )
        {
            $this->min = (float) $min;
        }

        if ( isset($max) )
        {
            $this->max = (float) $max;
        }

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_float_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Float Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxValue( $max )
    {
        $value = (float) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (float) $value;
    }

    public function setMinValue( $min )
    {
        $value = (float) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (float) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        $floatValue = (float) $value;

        if ( !UTIL_Validator::isFloatValid($value) )
        {
            return false;
        }

        if ( isset($this->min) && $floatValue < (float) $this->min )
        {
            return false;
        }

        if ( isset($this->max) && $floatValue > (float) $this->max )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        		
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
                var pattern = " . $this->pattern . ";
        		
            	if( !pattern.test( value ) )
            	{
            		throw " . json_encode($this->getError()) . ";
        		}
        ";

        if ( isset($this->min) )
        {
            $js .= "
            if( parseFloat(value) < " . $this->min . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        if ( isset($this->max) )
        {
            $js .= "
            if( parseFloat(value) > " . $this->max . " )
            {
            	throw " . json_encode($this->getError()) . ";
            }
           ";
        }

        $js .= "}
    		}";

        return $js;
    }
}

class DateValidator extends PEEP_Validator
{
    /**
     * @var int
     */
    private $minYear;
    /**
     * @var int
     */
    private $maxYear;
    /**
     * @var string
     */
    private $dateFormat = UTIL_DateTime::DEFAULT_DATE_FORMAT;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $minYear = null, $maxYear = null )
    {
        if ( isset($minYear) )
        {
            $this->setMinYear($minYear);
        }

        if ( isset($maxYear) )
        {
            $this->setMaxYear($maxYear);
        }

        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_date_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Date Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxYear( $maxYear )
    {
        $value = (int) $maxYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect max year value!');
        }

        $this->maxYear = (int) $value;
    }

    public function setDateFormat( $dateFormat )
    {
        $format = trim($dateFormat);

        if ( empty($format) )
        {
            throw new InvalidArgumentException('Incorrect argument `$format`!');
        }

        $this->dateFormat = trim($format);
    }

    public function setMinYear( $minYear )
    {
        $value = (int) $minYear;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Incorrect min year value!');
        }

        $this->minYear = (int) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        $date = UTIL_DateTime::parseDate($value, $this->dateFormat);

        if ( $date === null )
        {
            return false;
        }

        if ( !UTIL_Validator::isDateValid($date[UTIL_DateTime::PARSE_DATE_MONTH], $date[UTIL_DateTime::PARSE_DATE_DAY], $date[UTIL_DateTime::PARSE_DATE_YEAR]) )
        {
            return false;
        }

        if ( !empty($this->maxYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] > $this->maxYear )
        {
            return false;
        }

        if ( !empty($this->minYear) && $date[UTIL_DateTime::PARSE_DATE_YEAR] < $this->minYear )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}

class CaptchaValidator extends PEEP_Validator
{
    protected $jsObjectName = null;

    public function __construct()
    {
        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_captcha_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Captcha Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        return UTIL_Validator::isCaptchaValid($value);
    }

    public function getJsValidator()
    {
        if ( empty($this->jsObjectName) )
        {
            return "{
                    validate : function( value ){
            },
                    getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
            }";
        }
        else
        {
            return "{
                 
                    validate : function( value )
                    {
                        if( !window." . $this->jsObjectName . ".validateCaptcha() )
                        {
                            throw " . json_encode($this->getError()) . ";
                        }
                    },
                    
                    getErrorMessage : function()
                    {
                        return " . json_encode($this->getError()) . ";
                    }
            }";
        }
    }
}

class RangeValidator extends PEEP_Validator
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;
    /**
     * Class constructor.
     *
     */
    public function __construct()
    {
        $errorMessage = PEEP::getLanguage()->text('base', 'form_validator_range_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Range Validator Error!';
        }
        
        $this->setErrorMessage($errorMessage);
    }
    
    public function setMaxValue( $max )
    {
        $value = (int) $max;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty max value!');
        }

        $this->max = (int) $value;
    }

    public function setMinValue( $min )
    {
        $value = (int) $min;

        if ( empty($value) )
        {
            throw new InvalidArgumentException('Empty min value!');
        }

        $this->min = (int) $value;
    }

    /**
     * @see PEEP_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        // doesn't check empty values
        if ( $value === null )
        {
            return true;
        }
        
        if ( is_string($value) && mb_strlen(trim($value)) === 0 )
        {
            return true;
        }
        
        if ( is_array($value) )
        {
            $value = implode('-', $value);
        }
        
        return $this->checkValue($value);
    }

    public function checkValue( $value )
    {
        $value = trim($value);
        
        if ( empty($value) )
        {
            return false;
        }
        
        $valArray = explode('-', $value);

        if ( empty($valArray) || empty($valArray[0]) || empty($valArray[1]) )
        {
            return false;
        }

        if ($valArray[0] > $valArray[1])
        {
            return false;
        }
        
        if ( isset($this->min) && ($valArray[0] < (int) $this->min || $valArray[1] < (int) $this->min) )
        {
            return false;
        }

        if ( isset($this->max) && ($valArray[0] > (int) $this->max || $valArray[1] > (int) $this->max) )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            
        	validate : function( value )
        	{
        		var self = this;
        	
        		// doesn't check empty values
        		if( !value || $.trim( value ).length == 0 || ( $.isArray(value) && value.length == 0 ) )
        		{
        			return;
        		}
        		
        		if( $.isArray(value) )
        		{
        			$.each( value,
                        function( i, item )
                        {
                        	self.checkValue( item );
                        } );
        		}
        		else
        		{
        			this.checkValue( value );
        		}
        	},
    		";

        $js .= "
        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		},
        ";

        $js .= "
            checkValue : function( value )
            {
        ";

        if (isset($this->min) || isset($this->max))
        {
            if ( isset($this->min) )
            {
                $js .= "
                if( $.trim(value) < " . $this->min . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }

            if ( isset($this->max) )
            {
                $js .= "
                if( $.trim(value) > " . $this->max . " )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
            }
        }
        else
        {
            $js .= "if( $.trim(value).length == 0 )
                {
                    throw " . json_encode($this->getError()) . ";
                }
               ";
        }

        $js .= "}
    		}";

        return $js;
    }
}