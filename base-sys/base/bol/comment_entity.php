<?php

class BOL_CommentEntity extends PEEP_Entity
{
    /**
     * @var string
     */
    public $entityType;
    /**
     * @var integer
     */
    public $entityId;
    /**
     * @var string
     */
    public $pluginKey;
    /**
     * @var boolean
     */
    public $active = 1;

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function setEntityType( $entityType )
    {
        $this->entityType = trim($entityType);
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive( $active )
    {
        $this->active = (bool) $active;
    }

    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    public function setPluginKey( $pluginKey )
    {
        $this->pluginKey = $pluginKey;
    }
}

