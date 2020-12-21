<?php

class BOL_Vote extends PEEP_Entity
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
    public $vote;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var timeStamp
     */
    public $timeStamp;

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return (int) $this->entityId;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer
     */
    public function getVote()
    {
        return (int) $this->vote;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return (int) $this->userId;
    }

    /**
     * @return integer
     */
    public function getTimeStamp()
    {
        return (int) $this->timeStamp;
    }

    /**
     * @param integer $entityId
     * @return BOL_Vote
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @param string $entityType
     * @return BOL_Vote
     */
    public function setEntityType( $entityType )
    {
        $this->entityType = trim($entityType);
        return $this;
    }

    /**
     * @param integer $vote
     * @return BOL_Vote
     */
    public function setVote( $vote )
    {
        $this->vote = (int) $vote;
        return $this;
    }

    /**
     * @param integer $userId
     * @return BOL_Vote
     */
    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
        return $this;
    }

    /**
     * @param integer $timeStamp
     * @return BOL_Vote
     */
    public function setTimeStamp( $timeStamp )
    {
        $this->timeStamp = (int) $timeStamp;
        return $this;
    }
}