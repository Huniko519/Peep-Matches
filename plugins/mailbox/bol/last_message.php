<?php

class MAILBOX_BOL_LastMessage extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $conversationId;
    /**
     * @var integer
     */
    public $initiatorMessageId;
    /**
     * @var integer
     */
    public $interlocutorMessageId = 0;
}