<?php

class BOL_RestrictedUsernames extends PEEP_Entity
{
    /**
     * @var string
     */
    public $username;

    /**
     * @param string $username
     * @return BOL_RestrictedUsernames
     */
    public function setRestrictedUsername( $username )
    {
        $this->username = $username;

        return $this;
    }
}
