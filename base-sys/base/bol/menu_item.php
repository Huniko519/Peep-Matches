<?php

class BOL_MenuItem extends PEEP_Entity
{
    /**
     * @var string
     */
    public $prefix;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $documentKey = '';
    /**
     * @var string
     */
    public $type;
    /**
     * @var integer
     */
    public $order;
    /**
     * @var string
     */
    public $routePath;
    /**
     * @var string
     */
    public $externalUrl;
    /**
     * @var boolean
     */
    public $newWindow;
    /**
     * @var int
     */
    public $visibleFor = 3;

    /**
     * @return string
     */
    public function getDocumentKey()
    {
        return $this->documentKey;
    }

    /**
     * @return int
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
     * @return integer
     */
    public function getOrder()
    {
        return (int) $this->order;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->routePath;
    }

    /**
     * @return string
     */
    public function getExternalUrl()
    {
        return $this->externalUrl;
    }

    /**
     * @return boolean
     */
    public function getNewWindow()
    {
        return (boolean) $this->newWindow;
    }

    /**
     * @param string $documentKey
     * @return BOL_MenuItem
     */
    public function setDocumentKey( $documentKey )
    {
        $this->documentKey = trim($documentKey);
        return $this;
    }

    /**
     * @param string $name
     * @return BOL_MenuItem
     */
    public function setKey( $key )
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * @param integer $order
     * @return BOL_MenuItem
     */
    public function setOrder( $order )
    {
        $this->order = (int) $order;
        return $this;
    }

    /**
     * @param string $type
     * @return BOL_MenuItem
     */
    public function setType( $type )
    {
        $this->type = trim($type);
        return $this;
    }

    /**
     * @param string $routePath
     * @return BOL_MenuItem
     */
    public function setRoutePath( $routePath )
    {
        $this->routePath = trim($routePath);
        return $this;
    }

    /**
     * @param string $externalUrl
     * @return BOL_MenuItem
     */
    public function setExternalUrl( $externalUrl )
    {
        $this->externalUrl = trim($externalUrl);
        return $this;
    }

    /**
     * @param boolean $newWindow
     * @return BOL_MenuItem
     */
    public function setNewWindow( $newWindow )
    {
        $this->newWindow = (bool) $newWindow;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
    }

    /**
     * 
     * @return integer
     */
    public function getVisibleFor()
    {
        return $this->visibleFor;
    }

    public function setVisibleFor( $visibleFor )
    {
        $this->visibleFor = $visibleFor;

        return $this;
    }
}
