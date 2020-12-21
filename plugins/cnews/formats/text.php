<?php

class CNEWS_FORMAT_Text extends CNEWS_CLASS_Format
{
    public function __construct($vars, $formatName = null) 
    {
        parent::__construct($vars, $formatName);
        
        $defaults = array(
            "text" => null,
            "status" => null
        );
        
        $this->vars = array_merge($defaults, $this->vars);
       
        $this->assign("text", $this->vars["text"] ? $this->vars["text"] : $this->vars["status"]);
    }
}
