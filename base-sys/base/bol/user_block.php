<?php

class BOL_UserBlock extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;

    /**
     * @var integer
     */
    public $blockedUserId;

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * @return string
     */
    public function getBlockedUserId()
    {
        return $this->blockedUserId;
    }


    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function setBlockedUserId( $blockedUserId )
    {
        $this->blockedUserId = (int) $blockedUserId;
    }
}

