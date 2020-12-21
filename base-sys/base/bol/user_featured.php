<?php

class BOL_UserFeatured extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;

    public function setUserId( $id )
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}