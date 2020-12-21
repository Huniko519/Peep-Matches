<?php

class BOL_Avatar extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $hash;
    
    /**
     * @var string
     */
    public $status = 'active';

    /**
     *
     * @return integer
     */
    public function getUserId()
    {
        return (int) $this->userId;
    }

    /**
     *
     * @return integer
     */
    public function getHash()
    {
        return (int) $this->hash;
    }
    
    /**
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
