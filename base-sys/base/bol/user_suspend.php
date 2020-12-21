<?php

class BOL_UserSuspend extends PEEP_Entity
{
    /**
     * 
     * @var int
     */
    public $userId, $timestamp, $message = '';

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return BOL_UserSuspend
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTimestamp()
    {
        return $this->$timestamp;
    }

    /**
     * @return BOL_UserSuspend
     */
    public function setTimestamp( $timestamp )
    {
        $this->timestamp = $timestamp;

        return $this;
    }
    
    public function setMessage( $message )
    {
        $this->message = $message;

        return $this;
    }
}