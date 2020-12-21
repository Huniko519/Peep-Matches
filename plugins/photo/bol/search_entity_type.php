<?php

class PHOTO_BOL_SearchEntityType extends PEEP_Entity
{
    public $entityType;

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function setEntityType( $value )
    {
        $this->entityType = $value;

        return $this;
    }
}
