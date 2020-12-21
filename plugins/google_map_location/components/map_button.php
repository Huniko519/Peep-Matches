<?php


class GOOGLELOCATION_CMP_MapButton extends PEEP_Component
{
   

    public function __construct()
    {
        parent::__construct();
    
      $this->assign("go_to_map",PEEP::getRouter()->urlForRoute("googlelocation_user_map"));
    }

  
}