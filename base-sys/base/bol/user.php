<?php

class BOL_User extends PEEP_Entity
{
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;
    /**
     * @var integer
     */
    public $joinStamp;
    /**
     * @var integer
     */
    public $activityStamp;
    /**
     * @var string
     */
    public $accountType;
    /**
     * @var boolean
     */
    public $emailVerify = false;
    /**
     * @var int
     */
    public $joinIp = 0;

    /**
     * @return integer
     */
    public function getActivityStamp()
    {
        return $this->activityStamp;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return integer
     */
    public function getJoinStamp()
    {
        return $this->joinStamp;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return integer
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @return boolean
     */
    public function getEmailVerify()
    {
        return (boolean) $this->emailVerify;
    }

    /**
     * @return int
     */
    public function getJoinIp()
    {
        return (boolean) $this->joinIp;
    }

    /**
     * @param integer $activityStamp
     * @return BOL_User
     */
    public function setActivityStamp( $activityStamp )
    {
        $this->activityStamp = (int) $activityStamp;

        return $this;
    }

    /**
     * @param string $email
     * @return BOL_User
     */
    public function setEmail( $email )
    {
        $this->email = trim($email);

        return $this;
    }

    /**
     * @param integer $joinStamp
     * @return BOL_User
     */
    public function setJoinStamp( $joinStamp )
    {
        $this->joinStamp = (int) $joinStamp;

        return $this;
    }

    /**
     * @param string $password
     * @return BOL_User
     */
    public function setPassword( $password )
    {
        $this->password = trim($password);

        return $this;
    }

    /**
     * @param string $username
     * @return BOL_User
     */
    public function setUsername( $username )
    {
        $this->username = trim($username);

        return $this;
    }

    /**
     * @param integer $accountType
     * @return BOL_User
     */
    public function setAccountType( $accountType )
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * @return boolean
     */
    public function setEmailVerify( $emailVerify )
    {
        $this->emailVerify = (boolean) $emailVerify;
        return $this;
    }

        /**
     * @param int $ip
     * @return BOL_User
     */
    public function setJoinIp( $ip )
    {
        $this->joinIp = (int)$ip;

        return $this;
    }
}