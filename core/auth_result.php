<?php

class PEEP_AuthResult
{
    /**
     * General Failure.
     */
    const FAILURE = 0;

    /**
     * Identity not found failure.
     */
    const FAILURE_IDENTITY_NOT_FOUND = -1;

    /**
     * Invalid password failure.
     */
    const FAILURE_PASSWORD_INVALID = -2;

    /**
     * Authentication success.
     */
    const SUCCESS = 1;

    /**
     * @var integer
     */
    private $code;
    /**
     * @var array
     */
    private $messages;
    /**
     * @var integer
     */
    private $userId;

    /**
     * Constructor.
     */
    public function __construct( $code, $userId = null, array $messages = array() )
    {
        $code = (int) $code;

        if ( $code < self::FAILURE_PASSWORD_INVALID )
        {
            $code = self::FAILURE;
        }
        elseif ( $code > self::SUCCESS )
        {
            $code = self::SUCCESS;
        }

        $this->code = $code;

        if ( $userId != null )
        {
            $this->userId = (int) $userId;
        }

        $this->messages = $messages;
    }

    /**
     * Checks if authentication result is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        return ( $this->code > 0 ) ? true : false;
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}