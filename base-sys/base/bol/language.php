<?php

class BOL_Language extends PEEP_Entity
{
    /**
     * @var string
     */
    public $tag;
    /**
     * @var string
     */
    public $label;
    /**
     * @var int
     */
    public $order;
    /**
     * @var string
     */
    public $status;

    /**
     * @var integer
     */
    public $rtl = false;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * 
     * @return $this
     */
    public function setStatus( $status )
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return unknown
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param unknown_type $order
     * @return $this
     */
    public function setOrder( $order )
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string) $this->label;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $label
     * @return BOL_Language
     */
    public function setLabel( $label )
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $tag
     * @return BOL_Language
     */
    public function setTag( $tag )
    {
        $this->tag = $tag;

        return $this;
    }

    public function getRtl()
    {
        return $this->rtl;
    }

    public function setRtl( $rtl )
    {
        $this->rtl = (bool)$rtl;
    }
}

