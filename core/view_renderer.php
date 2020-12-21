<?php

class PEEP_ViewRenderer
{
    /**
     * @var PEEP_Smarty
     */
    private $smarty;

    /**
     * Singleton instance.
     *
     * @var PEEP_ViewRenderer
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_ViewRenderer
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->smarty = new PEEP_Smarty();
    }

    /**
     * Assigns list of values to template vars by reference.
     *
     * @param array $vars
     */
    public function assignVars( $vars )
    {
        foreach ( $vars as $key => $value )
        {
            $this->smarty->assignByRef($key, $vars[$key]);
        }
    }

    /**
     * Assigns value to template var by reference.
     *
     * @param string $key
     * @param mixed $value
     */
    public function assignVar( $key, $value )
    {
        $this->smarty->assignByRef($key, $value);
    }

    /**
     * Renders template using assigned vars and returns generated markup.
     *
     * @param string $template
     * @return string
     */
    public function renderTemplate( $template )
    {
        return $this->smarty->fetch($template);
    }

    /**
     * Returns assigned var value for provided var name.
     *
     * @param string $varName
     * @return mixed
     */
    public function getAssignedVar( $varName )
    {
        return $this->smarty->getTemplateVars($varName);
    }

    /**
     * Returns list of assigned var values.
     *
     * @return array
     */
    public function getAllAssignedVars()
    {
        return $this->smarty->getTemplateVars();
    }

    /**
     * Deletes all assigned template vars.
     */
    public function clearAssignedVars()
    {
        $this->smarty->clearAllAssign();
    }

    /**
     *
     * @param string $varName
     */
    public function clearAssignedVar( $varName )
    {
        $this->smarty->clearAssign($varName);
    }

    /**
     * Adds custom function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerFunction( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['function'][$name]) )
        {
            $this->smarty->registerPlugin('function', $name, $callback);
        }
    }

    /**
     * Removes custom function.
     *
     * @param string $name
     */
    public function unregisterFunction( $name )
    {
        $this->smarty->unregisterPlugin('function', $name);
    }

    /**
     * Adds custom block function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerBlock( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['block'][$name]) )
        {
            $this->smarty->registerPlugin('block', $name, $callback);
        }
    }

    /**
     * Removes block function.
     *
     * @param string $name
     */
    public function unregisterBlock( $name )
    {
        $this->smarty->unregisterPlugin('block', $name);
    }

    /**
     * Adds custom template modifier.
     * 
     * @param string $name
     * @param string $callback 
     */
    public function registerModifier( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['modifier'][$name]) )
        {
            $this->smarty->registerPlugin('modifier', $name, $callback);
        }
    }

    /**
     * Remopves template modifier.
     * 
     * @param string $name 
     */
    public function unregisterModifier( $name )
    {
        $this->smarty->unregisterPlugin('modifier', $name);
    }

    /**
     * Clears compiled templates.
     */
    public function clearCompiledTpl()
    {
        $this->smarty->clearCompiledTemplate();
    }
}