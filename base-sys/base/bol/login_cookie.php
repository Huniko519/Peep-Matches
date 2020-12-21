<?php

class BOL_LoginCookie extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $cookie;

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param integer $userId
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param string $cookie
     */
    public function setCookie( $cookie )
    {
        $this->cookie = $cookie;
        return $this;
    }
}