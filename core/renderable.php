<?php

abstract class PEEP_Renderable
{
    /**
     * List of added components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * List of registered forms.
     *
     * @var array
     */
    protected $forms = array();

    /**
     * List of assigned vars.
     *
     * @var array
     */
    protected $assignedVars = array();

    /**
     * Template path.
     *
     * @var string
     */
    protected $template;

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var array
     */
    private static $renderedClasses = array();

    /**
     * @var boolean
     */
    private static $devMode = false;

    /**
     * Getter for renderedClasses static property
     * 
     * @return array
     */
    public static function getRenderedClasses()
    {
        return self::$renderedClasses;
    }

    /**
     * Sets developer mode.
     * 
     * @param boolean $mode 
     */
    public static function setDevMode( $mode )
    {
        self::$devMode = (bool) $mode;
    }

    /**
     * Sets vomponent visibility.
     *
     * @param boolean $visible
     * @return PEEP_Renderable
     */
    public function setVisible( $visible )
    {
        $this->visible = (bool) $visible;
        return $this;
    }

    /**
     * Checks if component is visible.
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate( $template )
    {
        $this->template = $template;
    }

    /**
     * Adds component to renderable object.
     *
     * @param string $key
     * @param PEEP_Renderable $component
     */
    public function addComponent( $key, PEEP_Renderable $component )
    {
        $this->components[$key] = $component;
    }

    /**
     * Returns added component by key.
     *
     * @param string $key
     * @return PEEP_Component
     */
    public function getComponent( $key )
    {
        return ( isset($this->components[$key]) ? $this->components[$key] : null );
    }

    /**
     * Deletes added component.
     *
     * @param string $key
     */
    public function removeComponent( $key )
    {
        if ( isset($this->components[$key]) )
        {
            unset($this->components[$key]);
        }
    }

    /**
     * Adds form to renderable object.
     *
     * @param Form $form
     */
    public function addForm( Form $form )
    {
        $this->forms[$form->getName()] = $form;
    }

    /**
     * Returns added form by key.
     *
     * @param string $key
     * @return PEEP_Form
     */
    public function getForm( $name )
    {
        return ( isset($this->forms[$name]) ? $this->forms[$name] : null );
    }

    /**
     * Assigns variable.
     *
     * @param string $name
     * @param mixed $value
     */
    public function assign( $name, $value )
    {
        $this->assignedVars[$name] = $value;
    }

    /**
     * @param string $varName
     */
    public function clearAssign( $varName )
    {
        if ( isset($this->assignedVars[$varName]) )
        {
            unset($this->assignedVars[$varName]);
        }
    }

    public function onBeforeRender()
    {
        
    }

    /**
     * Returns rendered markup.
     *
     * @return string
     */
    public function render()
    {
        $this->onBeforeRender();
        if ( !$this->visible )
        {
            return '';
        }

        // TODO additional check
        if ( $this->template === null )
        {
            throw new LogicException('No template was provided for render! Class `' . get_class($this) . '`.');
        }

        $className = get_class($this);
        PEEP::getEventManager()->trigger(new PEEP_Event("core.performance_test", array("key" => "renderable_render.start", "params" => array("class" => $className))));

        $viewRenderer = PEEP_ViewRenderer::getInstance();

        $prevVars = $viewRenderer->getAllAssignedVars();

        if ( !empty($this->components) )
        {
            $renderedCmps = array();

            foreach ( $this->components as $key => $value )
            {
                $renderedCmps[$key] = $value->isVisible() ? $value->render() : '';
            }

            $viewRenderer->assignVars($renderedCmps);
        }

        if ( !empty($this->forms) )
        {
            $viewRenderer->assignVar('_peepForms_', $this->forms);
        }

        $viewRenderer->assignVars($this->assignedVars);

        $renderedMarkup = $viewRenderer->renderTemplate($this->template);

        $viewRenderer->clearAssignedVars();

        $viewRenderer->assignVars($prevVars);

        // temp dirty data collect for dev tool
        if ( self::$devMode )
        {
            self::$renderedClasses[$className] = $this->template;
        }

        PEEP::getEventManager()->trigger(new PEEP_Event("core.performance_test", array("key" => "renderable_render.end", "params" => array("class" => $className))));

        return $renderedMarkup;
    }
}
