<?php

class BOL_LanguageValue extends PEEP_Entity
{
    /**
     *
     * @var int
     */
    public $languageId;
    /**
     *
     * @var int
     */
    public $keyId;
    /**
     * 
     * @var string
     */
    public $value;

    /**
     * @return int
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $keyId
     * @return BOL_LanguageValue
     */
    public function setKeyId( $keyId )
    {
        $this->keyId = $keyId;

        return $this;
    }

    /**
     * @param int $languageId
     * @return BOL_LanguageValue
     */
    public function setLanguageId( $languageId )
    {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * @param string $value
     * @return BOL_LanguageValue
     */
    public function setValue( $value )
    {
        $this->value = trim($value);

        return $this;
    }
}
?>