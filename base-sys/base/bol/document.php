<?php

class BOL_Document extends PEEP_Entity
{
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $class;
    /**
     * @var string
     */
    public $action;
    /**
     * @var string
     */
    public $uri;
    /**
     * @var boolean
     */
    public $isStatic;
    /**
     * @var boolean
     */
    public $isMobile = 0;

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return boolean
     */
    public function getIsStatic()
    {
        return (bool) $this->isStatic;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $action
     * @return BOL_Document
     */
    public function setAction( $action )
    {
        $this->action = trim($action);
        return $this;
    }

    /**
     * @param string $class
     * @return BOL_Document
     */
    public function setClass( $class )
    {
        $this->class = trim($class);
        return $this;
    }

    /**
     * @param string $key
     * @return BOL_Document
     */
    public function setKey( $key )
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * @param boolean $isStatic
     * @return BOL_Document
     */
    public function setIsStatic( $isStatic )
    {
        $this->isStatic = (bool) $isStatic;
        return $this;
    }

    /**
     * @param string $uri
     * @return BOL_Document
     */
    public function setUri( $uri )
    {
        $this->uri = trim($uri);
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsMobile()
    {
        return (bool) $this->isMobile;
    }

    /**
     * @param bool $isMobile
     */
    public function setIsMobile( $isMobile )
    {
        $this->isMobile = (bool) $isMobile;
    }
}