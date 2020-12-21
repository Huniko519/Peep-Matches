<?php

class CNEWS_CLASS_Format extends PEEP_Component
{
    protected $vars = array();
    
    /**
     *
     * @var PEEP_Plugin
     */
    protected $plugin;
    
    public function __construct($vars, $formatName = null) 
    {
        parent::__construct();

        $this->vars = $vars;
        $this->plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey(get_class($this)));
        
        if ( $formatName !== null )
        {
            $this->setTemplate($this->getViewDir() . "formats" . DS . $formatName . ".html");
        }
    }
    
    public function render()
    {
        if ( $this->getTemplate() === null )
        {
            $template = PEEP::getAutoloader()->classToFilename(get_class($this), false);
            $this->setTemplate($this->getViewDir() . "formats" . DS . $template . '.html');
        }
        
        return parent::render();
    }
    
    protected function getLocalizedText( $value )
    {
        if ( !is_array($value) )
        {
            return $value;
        }

        list($prefix, $key) = explode("+", $value["key"]);
        
        return PEEP::getLanguage()->text($prefix, $key, $value['vars']);
    }
    
    protected function getUrl( $value )
    {
        if ( !is_array($value) )
        {
            return $value;
        }
        
        if ( PEEP::getRouter()->getRoute($value["routeName"]) === null )
        {
            return null;
        }
        
        return PEEP::getRouter()->urlForRoute($value["routeName"], empty($value["vars"]) ? array() : $value["vars"]);
    }
    
    protected function getViewDir()
    {
        return $this->plugin->getViewDir();
    }
}