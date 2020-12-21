<?php

class BOL_EntityTag extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $entityId;
    /**
     * @var string
     */
    public $entityType;
    /**
     * @var integer
     */
    public $tagId;
    /**
     * @var boolean
     */
    public $active = 1;

    /**
     * @return integer $entityId
     */
    public function getEntityId()
    {
        return (int) $this->entityId;
    }

    /**
     * @return string $entityType
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer $tagId
     */
    public function getTagId()
    {
        return (int) $this->tagId;
    }

    /**
     * @param string $entityId
     * @return BOL_EntityTag
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @param string $entityType
     * @return BOL_EntityTag
     */
    public function setEntityType( $entityType )
    {
        $this->entityType = trim($entityType);
        return $this;
    }

    /**
     * @param integer $tagId
     * @return BOL_EntityTag
     */
    public function setTagId( $tagId )
    {
        $this->tagId = (int) $tagId;
        return $this;
    }

    public function getActive()
    {
        return (bool) $this->active;
    }

    public function setActive( $active )
    {
        $this->active = (int) $active;
    }
}