<?php

class PCGALLERY_CMP_VisitorProfileWelcome extends PEEP_Component
{
    public function __construct( $params )
    {
if (PEEP::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
        }
       parent::__construct();
       
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('pcgallery')->getCmpViewDir() . 'visitor_profile_welcome.html');
    }


}