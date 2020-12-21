<?php

class BOL_AuthorizationGroup extends PEEP_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var boolean
     */
    public $moderated = true;

    public function getName()
    {
        return $this->name;
    }

    public function setName( $name )
    {
        $this->name = $name;
    }

    public function isModerated()
    {
        return (boolean) $this->moderated;
    }

    public function setModerated( $moderated )
    {
        $this->moderated = (boolean) $moderated;
    }
}
