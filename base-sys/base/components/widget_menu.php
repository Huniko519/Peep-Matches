<?php

class BASE_CMP_WidgetMenu extends PEEP_Component
{

    public function __construct( $items )
    {
        parent::__construct();

        $this->assign('items', $items);
        PEEP::getDocument()->addOnloadScript('PEEP.initWidgetMenu(' . json_encode($items) . ')');
    }
}