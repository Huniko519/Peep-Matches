<?php

class BASE_CMP_JoinButton extends PEEP_Component
{
    public function __construct( $params = array() )
    {
        parent::__construct();

        if (PEEP::getUser()->isAuthenticated())
        {
            $this->setVisible(false);
        }

        $this->assign('class', !empty($params['cssClass']) ? $params['cssClass'] : '' );
        $this->assign('url', PEEP::getRouter()->urlForRoute('base_join'));
    }
}