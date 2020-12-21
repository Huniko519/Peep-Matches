<?php

class PEEP_User
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->auth = PEEP_Auth::getInstance();

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
        else
        {
            $this->user = null;
        }
    }
    /**
     *
     * @var PEEP_Auth
     */
    private $auth;
    /**
     * Current user object;
     *
     * @var BOL_User
     */
    private $user;

    /**
     *
     * @param string $groupName
     * @param string $actionName
     * @param array $extra
     * @return boolean
     */
    public function isAuthorized( $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead\n"
                . PEEP_ErrorManager::getInstance()->debugBacktrace(), E_USER_WARNING);
        }

        return BOL_AuthorizationService::getInstance()->isActionAuthorized($groupName, $actionName, $extra);
    }

    /**
     *
     * @param PEEP_AuthAdapter $adapter
     * @return PEEP_AuthResult
     */
    public function authenticate( PEEP_AuthAdapter $adapter )
    {
        $result = $this->auth->authenticate($adapter);

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }

        return $result;
    }

    /**
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getId()
    {
        return ( $this->user === null ) ? 0 : $this->user->getId();
    }

    /**
     *
     * @return string
     */
    public function getEmail()
    {
        return ( $this->user === null ) ? '' : $this->user->email;
    }

    /**
     *
     * @return BOL_User
     */
    public function getUserObject()
    {
        return $this->user;
    }

    public function isAdmin()
    {
        return $this->isAuthorized(BOL_AuthorizationService::ADMIN_GROUP_NAME);
    }

    public function login( $userId )
    {
        $this->auth->login($userId);

        if ( $this->isAuthenticated() )
        {
            $this->user = BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
    }

    public function logout()
    {
        if ( $this->isAuthenticated() )
        {
            $this->auth->logout();
            $this->user = null;
        }
    }
}

