<?php

class COREVISITOR_CMP_VisitorInfoFirst extends PEEP_Component
{
    public function __construct( $params )
    {
        parent::__construct();
$this->userService = BOL_UserService::getInstance();
$this->assign('totalUsers', BOL_UserService::getInstance()->count(true));
    }
}