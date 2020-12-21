<?php

abstract class PEEP_AuthAdapter
{
    /**
     * Tries to authenticate user.
     *
     * @return PEEP_AuthResult
     */
    abstract function authenticate();
}