<?php

class PEEP_Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    protected $_fieldsHash;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @param int $id
     */
    public function setId( $id )
    {
        $this->id = (int) $id;

        return $this;
    }

    public function generateFieldsHash()
    {
        $this->_fieldsHash = array();
        $vars = get_object_vars($this);

        foreach ( $vars as $varName => $varValue )
        {
            if ( $varName != 'id' && !strstr($varName, '_fieldsHash') )
            {
                $this->_fieldsHash[$varName] = crc32($varValue);
            }
        }
    }

    public function getEntinyUpdatedFields()
    {
        $updatedFields = array();
        $vars = get_object_vars($this);
        
        foreach ( $vars as $varName => $varValue )
        {
            if ( !in_array($varName, array('_fieldsHash', 'id')) && (!isset($this->_fieldsHash[$varName]) || $this->_fieldsHash[$varName] !== crc32($varValue) ) )
            {
                $updatedFields[] = $varName;
            }
        }
        
        return $updatedFields;
    }
}
