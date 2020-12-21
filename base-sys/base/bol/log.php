<?php

class BOL_Log extends PEEP_Entity
{
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $timeStamp;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = $message;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType( $type )
    {
        $this->type = $type;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey( $key )
    {
        $this->key = $key;
    }

    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = $timeStamp;
    }
}