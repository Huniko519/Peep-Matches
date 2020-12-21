<?php

class EMOTICONS_BOL_EmoticonsDao extends PEEP_BaseDao
{
    CONST ORDER = 'order';
    CONST CODE = 'code';
    CONST CATEGORY = 'category';
    CONST IS_CAPTION = 'isCaption';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'EMOTICONS_BOL_Emoticons';
    }
    
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'emoticons_emoticons';
    }
    
    public function getAllEmoticons()
    {
        return $this->dbo->queryForObjectList('SELECT * FROM `' . $this->getTableName() . '` ORDER BY `' . self::ORDER . '`, `id`', $this->getDtoClassName());
    }
    
    public function updateEmoticonsOrder( $order )
    {
        if ( empty($order) )
        {
            return FALSE;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '` SET `' . self::ORDER . '` = CASE `id` ';
        
        foreach ( $order as $id => $value )
        {
            $sql .= "WHEN $id THEN $value ";
        }
        
        $sql .= 'END WHERE `id` IN(' . implode(',', array_map('intval', array_keys($order))) . ')';
        
        return $this->dbo->update($sql);
    }
    
    public function findSmileByCode( $code )
    {
        if ( empty($code) )
        {
            return NULL;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::CODE, $code);
        
        return $this->findObjectByExample($example);
    }
    
    public function getMaxOrder()
    {
        $sql = 'SELECT MAX(`' . self::ORDER . '`) FROM `' . $this->getTableName() . '`';
        
        return $this->dbo->queryForColumn($sql);
    }
    
    public function getMaxId()
    {
        $sql = 'SELECT MAX(`' . self::CATEGORY . '`) FROM `' . $this->getTableName() . '`';
        
        return $this->dbo->queryForColumn($sql);
    }
    
    public function findEmoticonsByCategory( $categoryId )
    {
        if ( empty($categoryId) )
        {
            return array();
        }
        
        $sql = 'SELECT * '
                . 'FROM `' . $this->getTableName() . '` '
                . 'WHERE `' . self::CATEGORY . '` = :category';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('category' => $categoryId));
    }
    
    public function deleteEmoticonsByCategory( $categoryId )
    {
        if ( empty($categoryId) )
        {
            return FALSE;
        }
        
        $example = new PEEP_Example();
        $example->andFieldEqual(self::CATEGORY, $categoryId);
        
        return $this->deleteByExample($example);
    }
    
    public function setSmileCaption( $smileId, $categoryId )
    {
        if ( empty($_POST['id']) || empty($_POST['categoryId']) )
        {
            return FALSE;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '` '
                . 'SET `' . self::IS_CAPTION . '` = 0 '
                . 'WHERE `' . self::CATEGORY . '` = :category; '
                . 'UPDATE `' . $this->getTableName() . '` '
                . 'SET `' . self::IS_CAPTION . '` = 1 '
                . 'WHERE `id` = :id';
        
        return (bool)$this->dbo->query($sql, array('id' => $smileId, 'category' => $categoryId));
    }
}


class EMOTICONS_BOL_Emoticons extends PEEP_Entity
{
    public $category;
    public $isCaption;
    public $order;
    public $code;
    public $name;
}
