<?php

class MAILBOX_BOL_Message extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $conversationId;
    /**
     * @var integer
     */
    public $timeStamp;
    /**
     * @var integer
     */
    public $senderId;
    /**
     * @var integer
     */
    public $recipientId;
    /**
     * @var string
     */
    public $text;
    /**
     * @var integer
     */
    public $recipientRead = 0;
    /**
     * @var integer
     */
    public $isSystem = 0;
    /**
     * @var integer
     */
    public $wasAuthorized = 0;
}