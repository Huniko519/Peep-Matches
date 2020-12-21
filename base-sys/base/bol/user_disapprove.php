<?php

class BOL_UserDisapprove extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * 
     * @return unknown_type
     */
    public function setUserId($userId)
    {
    	$this->userId = $userId;

    	return $this;
    }
    
    public function getUserId()
    {
    	return $this->userId;
    }
}
