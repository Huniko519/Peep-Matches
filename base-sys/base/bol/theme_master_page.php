<?php

class BOL_ThemeMasterPage extends PEEP_Entity
{
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var string
     */
    public $documentKey;
    /**
     * @var string
     */
    public $masterPage;

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
    public function getMasterPage()
    {
        return $this->masterPage;
    }

    /**
     * @return integer
     */
    public function getThemeId()
    {
        return (int) $this->themeId;
    }

    /**
     * @param string $documentKey
     * @return BOL_ThemeMasterPage
     */
    public function setDocumentKey( $documentKey )
    {
        $this->documentKey = trim($documentKey);
        return $this;
    }

    /**
     * @param string $masterPage
     * @return BOL_ThemeMasterPage
     */
    public function setMasterPage( $masterPage )
    {
        $this->masterPage = trim($masterPage);
        return $this;
    }

    /**
     * @param integer $themeId
     * @return BOL_ThemeMasterPage
     */
    public function setThemeId( $themeId )
    {
        $this->themeId = (int) $themeId;
        return $this;
    }
}

