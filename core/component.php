<?php

abstract class PEEP_Component extends PEEP_Renderable
{

    /**
     * Constructor.
     *
     * @param string $template
     */
    public function __construct( $template = null )
    {
        parent::__construct();

        // TODO remove everthing from constructor
        try
        {
            $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey(get_class($this)));
        }
        catch ( InvalidArgumentException $e )
        {
            $plugin = null;
        }

        if ( $template !== null && $plugin !== null )
        {
            $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
        }
    }

    public function render()
    {
        if ( $this->getTemplate() === null )
        {
            try
            {
                $plugin = PEEP::getPluginManager()->getPlugin(PEEP::getAutoloader()->getPluginKey(get_class($this)));
            }
            catch ( InvalidArgumentException $e )
            {
                $plugin = null;
            }

            if ( $plugin !== null )
            {
                $template = PEEP::getAutoloader()->classToFilename(get_class($this), false);
                $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
            }
        }

        return parent::render();
    }
}