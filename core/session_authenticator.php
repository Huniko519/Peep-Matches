<?php

class PEEP_SessionAuthenticator implements PEEP_IAuthenticator
{
    const USER_ID_SESSION_KEY = 'userId';

    public function __construct()
    {
        
    }

    /**
     * Checks if current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return ( PEEP::getSession()->isKeySet(self::USER_ID_SESSION_KEY) && $this->getUserId() > 0 );
    }

    /**
     * Returns current user id.
     * If user is not authenticated 0 returned.
     *
     * @return integer
     */
    public function getUserId()
    {
        return (int) PEEP::getSession()->get(self::USER_ID_SESSION_KEY);
    }

    /**
     * Logins user by provided user id.
     *
     * @param integer $userId
     */
    public function login( $userId )
    {
        PEEP::getSession()->set(self::USER_ID_SESSION_KEY, $userId);
    }

    /**
     * Logs out current user.
     */
    public function logout()
    {
        PEEP::getSession()->delete(self::USER_ID_SESSION_KEY);
    }

    /**
     * Returns auth id
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }
}