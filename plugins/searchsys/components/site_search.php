<?php

class SEARCHSYS_CMP_SiteSearch extends PEEP_Component
{
    public function __construct( )
    {
if( !PEEP::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
            return array();
        }
        parent::__construct();        
       
        $form = new SEARCHSYS_CLASS_ConsoleSearchForm();
        $this->addForm($form);
    }
 public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}