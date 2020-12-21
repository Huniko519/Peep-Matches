<?php

class FRIENDS_BOL_Friendship extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $friendId;
    /**
     * @var integer
     */
    public $status;
    /**
     * @var integer
     */
    public $timeStamp = 0;
    /**
     * @var boolean
     */
    public $viewed = 0;
    /**
     * @var boolean
     */
    public $active = 1;
    /**
     * @var boolean
     */
    public $notificationSent = 0;


    /**
     * @return FRIENDS_Friendship
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     *
     * @return FRIENDS_Friendship
     */
    public function setFriendId( $friendId )
    {
        $this->friendId = $friendId;

        return $this;
    }

    public function getFriendId()
    {
        return $this->friendId;
    }

    /**
     *
     * @return FRIENDS_Friendship
     */
    public function setStatus( $status )
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
}