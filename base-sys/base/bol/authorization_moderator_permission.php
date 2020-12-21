<?php
class BOL_AuthorizationModeratorPermission extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $moderatorId;
    /**
     * @var integer
     */
    public $groupId;

    /**
     *
     * @return BOL_AuthorizationModeratorPermission
     */
    public function setModeratorId( $moderatorId )
    {
        $this->moderatorId = $moderatorId;

        return $this;
    }

    public function getModeratorId()
    {
        return $this->moderatorId;
    }

    /**
     *
     * @return BOL_AuthorizationModeratorPermission
     */
    public function setGroupId( $groupId )
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }
}
