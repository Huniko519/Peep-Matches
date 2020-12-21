<?php

abstract class PEEP_ActionController extends PEEP_Renderable
{
    /**
     * Default controller action (used if action isn't provided).
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Constructor.
     */
    public function __construct()
    {
        
    }

    /**
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * @param string $action
     */
    public function setDefaultAction( $action )
    {
        $this->defaultAction = trim($action);
    }

    /**
     * Makes permanent redirect to the same controller and provided action.
     *
     * @param string $action
     */
    public function redirectToAction( $action )
    {
        $handlerAttrs = PEEP::getRequestHandler()->getHandlerAttributes();

        PEEP::getApplication()->redirect(PEEP::getRouter()->uriFor($handlerAttrs['controller'], trim($action)));
    }

    /**
     * Makes permanent redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null )
    {
        PEEP::getApplication()->redirect($redirectTo);
    }

    /**
     * Optional method. Called before action.
     */
    public function init()
    {
        
    }

    /**
     * Sets custom document key for current page.
     *
     * @param string $key
     */
    public function setDocumentKey( $key )
    {
        PEEP::getApplication()->setDocumentKey($key);
    }

    /**
     * Returns document key for current page.
     * 
     * @return string
     */
    public function getDocumentKey()
    {
        return PEEP::getApplication()->getDocumentKey();
    }

    /**
     * Sets page heading.
     * @param string $heading
     */
    public function setPageHeading( $heading )
    {
        PEEP::getDocument()->setHeading(trim($heading));
    }

    /**
     * Sets page heading icon class.
     *
     * @param string $class
     */
    public function setPageHeadingIconClass( $class )
    {
        PEEP::getDocument()->setHeadingIconClass($class);
    }

    /**
     * @param string $title
     */
    public function setPageTitle( $title )
    {
        PEEP::getDocument()->setTitle(trim($title));
    }

    /**
     * @param string $desc
     */
    public function setPageDescription( $desc )
    {
        PEEP::getDocument()->setDescription($desc);
    }

    /**
     * @param array $keywords
     */
    public function setKeywords( $keywords )
    {
        PEEP::getDocument()->setKeywords($keywords);
    }
}
