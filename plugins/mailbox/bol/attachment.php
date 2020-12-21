<?php

class MAILBOX_BOL_Attachment extends PEEP_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $messageId;
    /**
     * @var int
     */
    public $hash;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var int
     */
    public $fileSize;
}