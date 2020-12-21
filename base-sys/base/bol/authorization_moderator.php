<?php
class BOL_AuthorizationModerator extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     *
     * @param int $id
     * @return BOL_AuthorizationModerator;
     */
    public function setUserId( $id )
    {
        $this->userId = $id;

        return $this;
    }
}
