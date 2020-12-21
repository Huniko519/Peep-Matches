<?php

class GOOGLEAUTH_CMP_ConnectButton extends PEEP_Component
{

  public function render()
    {
     $this->assign('url',GOOGLEAUTH_BOL_Service::getInstance()->generateOAuthUri());
     return parent::render();
    }
}