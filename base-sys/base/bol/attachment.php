<?php

class BOL_Attachment extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var ineteger
     */
    public $addStamp;
    /**
     * @var int
     */
    public $status;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $origFileName;
    /**
     * @var int
     */
    public $size;
    /**
     * @var string
     */
    public $bundle;
    /**
     * @var string
     */
    public $pluginKey;

    public function getUserId()
    {
        return (int) $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getAddStamp()
    {
        return (int) $this->addStamp;
    }

    public function setAddStamp( $addStamp )
    {
        $this->addStamp = (int) $addStamp;
    }

    public function getStatus()
    {
        return (int) $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = (int) $status;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName( $fileName )
    {
        $this->fileName = trim($fileName);
    }

    public function getSize()
    {
        return (int) $this->size;
    }

    public function setSize( $size )
    {
        $this->size = (int) $size;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle( $bundle )
    {
        $this->bundle = trim($bundle);
    }

    public function getOrigFileName()
    {
        return $this->origFileName;
    }

    public function setOrigFileName( $origFileName )
    {
        $this->origFileName = trim($origFileName);
    }

    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    public function setPluginKey( $pluginKey )
    {
        $this->pluginKey = $pluginKey;
    }
}

