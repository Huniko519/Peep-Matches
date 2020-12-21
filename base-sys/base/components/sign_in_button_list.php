<?php

class BASE_CMP_SignInButtonList extends PEEP_Component
{
    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $cmp = new BASE_CMP_ConnectButtonList();

        $this->addComponent('cmp', $cmp);

        if( !$cmp->isVisible() )
        {
            $this->setVisible(false);
        }
    }
}