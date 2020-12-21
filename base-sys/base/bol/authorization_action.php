<?php

class BOL_AuthorizationAction extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $groupId;
    /**
     * @var string
     */
    public $name;
    /**
     * @var boolean
     */
    public $availableForGuest = true;

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function setGroupId( $groupId )
    {
        $this->groupId = $groupId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName( $name )
    {
        $this->name = $name;
    }

    public function isAvailableForGuest()
    {
        return (bool) $this->availableForGuest;
    }

    public function setAvailableForGuest( $availableForGuest )
    {
        $this->availableForGuest = (bool) $availableForGuest;
    }
}
