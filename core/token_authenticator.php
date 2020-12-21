<?php

class PEEP_TokenAuthenticator implements PEEP_IAuthenticator
{
    /**
     * @var BOL_UserService
     */
    private $service;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $token;

    public function __construct( $token = null )
    {
        $this->service = BOL_UserService::getInstance();

        $this->userId = 0;

        $this->token = $token;

        if ( $token !== null )
        {
            $this->userId = (int) $this->service->findUserIdByAuthToken($token);
        }
    }

    /**
     * Checks if current user is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->userId !== 0;
    }

    /**
     * Returns current user id.
     * If user is not authenticated 0 returned.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Logins user by provided user id.
     *
     * @param integer $userId
     */
    public function login( $userId )
    {
        $this->userId = $userId;
        $this->token = $this->service->addTokenForUser($this->userId);
    }

    /**
     * Logs out current user.
     */
    public function logout()
    {
        if ( $this->isAuthenticated() )
        {
            $this->service->deleteTokenForUser($this->getUserId());
            $this->token = null;
        }
    }

    /**
     * Returns auth id
     */
    public function getId()
    {
        return $this->token;
    }
}
