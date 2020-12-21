<?php

class BOL_Mail extends PEEP_Entity
{
    /**
     * @var string
     */
    public $recipientEmail;
    /**
     * @var string
     */
    public $senderEmail;
    /**
     * @var string
     */
    public $senderName;
    /**
     * @var string
     */
    public $subject;
    /**
     * @var string
     */
    public $textContent;
    /**
     * @var string
     */
    public $htmlContent;
    /**
     * @var int
     */
    public $sentTime;
    /**
     * @var int
     */
    public $priority;

    /**
     *
     * @var int
     */
    public $senderSuffix;


     /**
     *
     * @var boolean
     */
    public $sent = 0;

}
