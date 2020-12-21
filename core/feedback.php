<?php

final class PEEP_Feedback
{
    /* feedback messages types */
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';

    /**
     * @var array
     */
    private $feedback;
    /**
     * Singleton instance.
     * 
     * @var PEEP_Feedback
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Feedback
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $session = PEEP::getSession();

        if ( $session->isKeySet('peep_messages') )
        {
            $this->feedback = $session->get('peep_messages');
            $session->delete('peep_messages');
        }
        else
        {
            $this->feedback = array(
                'error' => array(),
                'info' => array(),
                'warning' => array()
            );
        }
    }

    /**
     * Adds message to feedback.
     *
     * @param string $message
     * @param string $type
     * @return PEEP_Feedback
     */
    private function addMessage( $message, $type = 'info' )
    {
        if ( $type !== self::TYPE_ERROR && $type !== self::TYPE_INFO && $type !== self::TYPE_WARNING )
        {
            throw new InvalidArgumentException('Invalid message type `' . $type . '`!');
        }

        $this->feedback[$type][] = $message;

        return $this;
    }

    /**
     * Adds error message to feedback.
     *
     * @param string $message
     */
    public function error( $message )
    {
        $this->addMessage($message, self::TYPE_ERROR);
    }

    /**
     * Adds info message to feedback.
     *
     * @param string $message
     */
    public function info( $message )
    {
        $this->addMessage($message, self::TYPE_INFO);
    }

    /**
     * Adds warning message to feedback.
     *
     * @param string $message
     */
    public function warning( $message )
    {
        $this->addMessage($message, self::TYPE_WARNING);
    }

    /**
     * Returns whole list of registered messages.
     *
     * @return array
     */
    public function getFeedback()
    {
        $feedback = $this->feedback;

        $this->feedback = null;

        return $feedback;
    }

    /**
     * System method. Don't call it.
     */
    public function __destruct()
    {
        if ( $this->feedback !== null )
        {
            PEEP::getSession()->set('peep_messages', $this->feedback);
        }
    }
}

