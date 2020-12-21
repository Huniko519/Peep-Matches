<?php

class BOL_AuthToken extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var ineteger
     */
    public $token;
    /**
     * @var bool
     */
    public $timeStamp;

    public function getUserId()
    {
        return (int) $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getToken()
    {
        return trim($this->token);
    }

    public function setToken( $token )
    {
        $this->token = trim($token);
    }

    public function getTimeStamp()
    {
        return (int) $this->timeStamp;
    }

    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = (int) $timeStamp;
    }
}

