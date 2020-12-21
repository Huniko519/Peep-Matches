<?php

class PEEP_Auth
{
    /**
     * @var PEEP_IAuthenticator
     */
    private $authenticator;
    /**
     * Singleton instance.
     *
     * @var PEEP_Auth
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Auth
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

    }

    /**
     * @return PEEP_IAuthenticator
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * @param PEEP_IAuthenticator $authenticator
     */
    public function setAuthenticator( PEEP_IAuthenticator $authenticator )
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Tries to authenticate user using provided adapter.
     *
     * @param PEEP_AuthAdapter $adapter
     * @return PEEP_AuthResult
     */
    public function authenticate( PEEP_AuthAdapter $adapter )
    {
        $result = $adapter->authenticate();

        if ( !( $result instanceof PEEP_AuthResult ) )
        {
            throw new LogicException('Instance of PEEP_AuthResult expected!');
        }

        if ( $result->isValid() )
        {
            $this->login($result->getUserId());
        }

        return $result;
    }

    /**
     * Checks if current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticator->isAuthenticated();
    }

    /**
     * Returns current user id.
     * If user is not authenticated 0 returned.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->authenticator->getUserId();
    }

    /**
     * Logins user by provided user id.
     *
     * @param integer $userId
     * @return string
     */
    public function login( $userId )
    {
        $userId = (int) $userId;

        if ( $userId < 1 )
        {
            throw new InvalidArgumentException('invalid userId');
        }

        $event = new PEEP_Event(PEEP_EventManager::ON_BEFORE_USER_LOGIN, array('userId' => $userId));
        PEEP::getEventManager()->trigger($event);

        $this->authenticator->login($userId);

        $event = new PEEP_Event(PEEP_EventManager::ON_USER_LOGIN, array('userId' => $userId));
        PEEP::getEventManager()->trigger($event);
    }

    /**
     * Logs out current user.
     */
    public function logout()
    {
        if ( !$this->isAuthenticated() )
        {
            return;
        }

        $event = new PEEP_Event(PEEP_EventManager::ON_USER_LOGOUT, array('userId' => $this->getUserId()));
        PEEP::getEventManager()->trigger($event);

        $this->authenticator->logout();
    }

    /**
     * Returns auth id
     *
     * @return string
     */
    public function getId()
    {
        return $this->authenticator->getId();
    }
}