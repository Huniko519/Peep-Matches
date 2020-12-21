<?php

class BOL_Rate extends PEEP_Entity
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
    public $userId;
    /**
     * @var integer
     */
    public $score;
    /**
     * @var integer
     */
    public $timeStamp;
    /**
     * @var integer
     */
    public $active;

    /**
     * @return integer $entityId
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return strign $entityType
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return integer $score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return integer $timeStamp
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @param integer $entityId
     * @return BOL_Rate
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @param string $entityType
     * @return BOL_Rate
     */
    public function setEntityType( $entityType )
    {
        $this->entityType = trim($entityType);
        return $this;
    }

    /**
     * @param integer $userId
     * @return BOL_Rate
     */
    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
        return $this;
    }

    /**
     * @param integer $score
     * @return BOL_Rate
     */
    public function setScore( $score )
    {
        $this->score = (int) $score;
        return $this;
    }

    /**
     * @param integer $timeStamp
     * @return BOL_Rate
     */
    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = (int) $timeStamp;
        return $this;
    }

    public function getActive()
    {
        return (bool) $this->active;
    }

    public function setActive( $active )
    {
        $this->active = $active ? 1 : 0;
    }
}