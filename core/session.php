<?php

class PEEP_Session
{
    /**
     * Singleton instance.
     *
     * @var PEEP_Session
     */
    private static $classInstance;
    private static $protectedKeys = array('session.home_url', 'session.user_agent');

    private function __construct()
    {
        if ( session_id() === '' )
        {
            //disable transparent sid support
            ini_set('session.use_trans_sid', '0');
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        }
    }

    public function getName()
    {
        return md5(PEEP_URL_HOME);
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Session
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function start()
    {
        //TODO: maybe session_destroy ?
        session_name($this->getName());

        $cookie = session_get_cookie_params();
        $cookie['httponly'] = true;

        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

        session_start();

        if ( !isset($_SESSION['session.home_url']) )
        {
            $_SESSION['session.home_url'] = PEEP_URL_HOME;
        }
        else if ( strcmp($_SESSION['session.home_url'], PEEP_URL_HOME) )
        {
            $this->regenerate();
        }

        $userAgent = PEEP::getRequest()->getUserAgentName();

        if ( isset($_SESSION['session.user_agent']) )
        {
            if ( $_SESSION['session.user_agent'] !== $userAgent )
            {
                $this->regenerate();
            }
        }
        else
        {
            $_SESSION['session.user_agent'] = $userAgent;
        }
    }

    public function regenerate()
    {
        session_regenerate_id();

        $_SESSION = array();

        if ( isset($_COOKIE[$this->getName()]) )
        {
            $_COOKIE[$this->getName()] = $this->getId();
        }
    }

    public function getId()
    {
        return session_id();
    }

    public function set( $key, $value )
    {
        if ( in_array($key, self::$protectedKeys) )
        {
            throw new Exception('Attempt to set protected key');
        }

        $_SESSION[$key] = $value;
    }

    public function get( $key )
    {
        if ( !isset($_SESSION[$key]) )
        {
            return null;
        }

        return $_SESSION[$key];
    }

    public function isKeySet( $key )
    {
        return isset($_SESSION[$key]);
    }

    public function delete( $key )
    {
        unset($_SESSION[$key]);
    }
}
