<?php

class CNEWS_CLASS_Identifier
{
    public $id;
    public $type;
    
    public function __construct($type, $id)
    {
        $this->type = trim($type);
        $this->id = $id;
    }
}