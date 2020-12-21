<?php

class BOL_LanguageKey extends PEEP_Entity
{
    /**
     * @var int
     */
    public $prefixId;
    /**
     * @var string
     */
    public $key;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getPrefixId()
    {
        return $this->prefixId;
    }

    /**
     * @param string $key
     * @return BOL_LanguageKey
     */
    public function setKey( $key )
    {
        $this->key = trim($key);

        return $this;
    }

    /**
     * @param int $prefixId
     * @return BOL_LanguageKey
     */
    public function setPrefixId( $prefixId )
    {
        $this->prefixId = $prefixId;

        return $this;
    }
}