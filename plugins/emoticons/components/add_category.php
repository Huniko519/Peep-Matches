<?php

class EMOTICONS_CMP_AddCategory extends PEEP_Component
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addForm(new EMOTICONS_CLASS_AddCategoryForm());
    }
}
