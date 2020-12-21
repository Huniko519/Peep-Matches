<?php


class EMOTICONS_CMP_Add extends PEEP_Component
{
    public function __construct( $categoryId )
    {
        parent::__construct();
        
        $this->addForm(new EMOTICONS_CLASS_AddForm($categoryId));
    }
}
