<?php

class SEARCHSYS_CMP_ListResultItem  extends PEEP_Component
{
    public function __construct( $data )
    {
        parent::__construct();

        $this->assign("data", $data);
    }
}