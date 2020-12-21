<?php

class BOL_EmailVerify extends PEEP_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var int
     */
    public $createStamp = 0;
    /**
     * @var string
     */
    public $type;
}
