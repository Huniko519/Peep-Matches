<?php

class BOL_Comment extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $commentEntityId;
    /**
     * @var string
     */
    public $message;
    /**
     * @var integer
     */
    public $createStamp;
    /**
     * @var string
     */
    public $attachment;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getCommentEntityId()
    {
        return $this->commentEntityId;
    }

    public function setCommentEntityId( $commentEntityId )
    {
        $this->commentEntityId = (int) $commentEntityId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = trim($message);
    }

    public function getCreateStamp()
    {
        return $this->createStamp;
    }

    public function setCreateStamp( $createStamp )
    {
        $this->createStamp = (int) $createStamp;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function setAttachment( $attachment )
    {
        $this->attachment = $attachment;
    }
}

