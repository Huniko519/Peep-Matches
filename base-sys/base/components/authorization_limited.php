<?php

class BASE_CMP_AuthorizationLimited extends PEEP_Component
{
    public function __construct( $message )
    {
        parent::__construct();

        $this->assign('message', $message);
    }
}
