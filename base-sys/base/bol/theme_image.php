<?php

class BOL_ThemeImage extends PEEP_Entity
{
    /**
     * @var string
     */
    public $filename;

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     *
     * @param string $filename
     * @return BOL_ThemeImage
     */
    public function setFilename( $filename )
    {
        $this->filename = $filename;
        return $this;
    }
}