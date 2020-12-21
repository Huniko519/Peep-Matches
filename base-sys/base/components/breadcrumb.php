<?php

class BASE_CMP_Breadcrumb extends PEEP_Component
{
    public function __construct( $items, $title = '' )
    {
        parent::__construct();
        
        $this->items = $items;
        
        $this->assign('items', $items);
        $this->assign('title', $title);
    }
}