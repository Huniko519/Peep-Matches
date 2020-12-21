<?php

class BASE_CLASS_Mail
{
    private $state = array(
        'recipientEmailList' => array(),
        'sender' => null,
        'subject' => null,
        'textContent' => null,
        'htmlContent' => null,
        'sentTime' => null,
        'priority' => self::PRIORITY_NORMAL,
        'replyTo' => null,
        'senderSuffix' => null
    );

    const PRIORITY_HIDE = 1;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 5;

    public function __construct( array $state = null )
    {
        if ( !empty($state) && is_array($state) )
        {
            $this->state = array_merge($this->state, $state);
        }
    }

    /**
     *
     * @param $email
     * @return BASE_CLASS_Mail
     */
    public function addRecipientEmail( $email )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['recipientEmailList'][] = $email;

        return $this;
    }

    /**
     *
     * @param $email
     * @param $name
     * @return BASE_CLASS_Mail
     */
    public function setReplyTo ( $email, $name = '' )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['replyTo'] = array($email, $name);

        return $this;
    }

    /**
     *
     * @param $email
     * @param $name
     * @return BASE_CLASS_Mail
     */
    public function setSender ( $email, $name = '' )
    {
        if ( !UTIL_Validator::isEmailValid($email) )
        {
            throw new InvalidArgumentException('Invalid argument `$email`');
        }

        $this->state['sender'] = array( $email, $name );

        return $this;
    }

    /**
     *
     * @param $subject
     * @return BASE_CLASS_Mail
     */
    public function setSubject( $subject )
    {
        if ( !trim($subject) )
        {
            throw new InvalidArgumentException('Invalid argument `$subject`');
        }

        $this->state['subject'] = $subject;

        return $this;
    }

    /**
     *
     * @param $content
     * @return BASE_CLASS_Mail
     */
    public function setTextContent( $content )
    {
        if ( !trim($content) )
        {
            throw new InvalidArgumentException('Invalid argument `$content`');
        }

        $this->state['textContent'] = $content;

        return $this;
    }

    /**
     *
     * @param $content
     * @return BASE_CLASS_Mail
     */
    public function setHtmlContent( $content )
    {
        $this->state['htmlContent'] = $content;

        return $this;
    }

    /**
     *
     * @param $time
     * @return BASE_CLASS_Mail
     */
    public function setSentTime( $time )
    {
        if ( !( $time = intval($time) ) )
        {
            throw new InvalidArgumentException('Invalid argument `$time`');
        }
        $this->state['sentTime'] = $time;

        return $this;
    }

    /**
     *
     * @param $priority
     * @return BASE_CLASS_Mail
     */
    public function setPriority( $priority )
    {
        if ( !( $priority = intval($priority) ) )
        {
            throw new InvalidArgumentException('Invalid argument `$priority`');
        }
        $this->state['priority'] = $priority;

        return $this;
    }

    public function setSenderSuffix( $suffix )
    {
        $this->state['senderSuffix'] = $suffix;

        return $this;
    }

    public function saveToArray()
    {
        return $this->state;
    }
}
