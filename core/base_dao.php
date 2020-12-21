<?php

abstract class PEEP_BaseDao
{
    const DEFAULT_CACHE_LIFETIME = false;

    public abstract function getTableName();

    public abstract function getDtoClassName();
    /**
     *
     * @var PEEP_Database
     */
    protected $dbo;

    protected function __construct()
    {
        $this->dbo = PEEP::getDbo();
    }

    /**
     * Finds and returns mapped entity item
     *
     * @param int $id
     * @return PEEP_Entity
     */
    public function findById( $id, $cacheLifeTime = 0, $tags = array() )
    {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `id` = ?';

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array((int) $id), $cacheLifeTime, $tags);
    }

    /**
     * Finds and returns mapped entity list
     *
     * @param array $idList
     * @return array
     */
    public function findByIdList( array $idList, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return array();
        }
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    public function findListByExample( PEEP_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT * FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    public function countByExample( PEEP_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    public function findObjectByExample( PEEP_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $example->setLimitClause(0, 1);
        $sql = 'SELECT * FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns all mapped entries of table
     *
     * @return array
     */
    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        $sql = 'SELECT * FROM ' . $this->getTableName();

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns count of all rows
     *
     * @return array
     */
    public function countAll()
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName();

        return $this->dbo->queryForColumn($sql);
    }

    /**
     * Delete entity by id. Returns affected rows
     * @param int $id
     * @return int
     */
    public function deleteById( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `id` = ?';
            $result = $this->dbo->delete($sql, array($id));
            $this->clearCache();
            return $result;
        }

        return 0;
    }

    /**
     * Deletes list of entities by id list. Returns affected rows
     *
     * @param array $idList
     * @return int
     */
    public function deleteByIdList( array $idList )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return;
        }
        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    public function deleteByExample( PEEP_Example $example )
    {
        if ( $example === null || mb_strlen($example->__toString()) === 0 )
        {
            throw new InvalidArgumentException('example must not be null or empty');
        }
        $sql = 'DELETE FROM ' . $this->getTableName() . $example;

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    /**
     * Saves and updates Entity item
     * throws InvalidArgumentException
     *
     * @param PEEP_Entity $entity
     * 
     * @throws InvalidArgumentException
     */
    public function save( $entity )
    {
        if ( $entity === null || !($entity instanceof PEEP_Entity) )
        {
            throw new InvalidArgumentException('Argument must be instance of PEEP_Entity and cannot be null');
        }

        $entity->id = (int) $entity->id;

        if ( $entity->id > 0 )
        {
            $this->dbo->updateObject($this->getTableName(), $entity);
        }
        else
        {
            $entity->id = NULL;
            $entity->id = $this->dbo->insertObject($this->getTableName(), $entity);
        }

        $this->clearCache();
    }

    public function saveDelayed( $entity )
    {
        if ( $entity === null || !($entity instanceof PEEP_Entity) )
        {
            throw new InvalidArgumentException('Argument must be instance of PEEP_Entity and cannot be null');
        }

        $entity->id = (int) $entity->id;

        if ( $entity->id > 0 )
        {
            $this->dbo->updateObject($this->getTableName(), $entity, 'id', true);
        }
        else
        {
            $entity->id = $this->dbo->insertObject($this->getTableName(), $entity, true);
        }

        $this->clearCache();
    }

    public function delete( $entity )
    {
        $this->deleteById($entity->id);
        $this->clearCache();
    }

    public function findIdByExample( PEEP_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $example->setLimitClause(0, 1);
        $sql = 'SELECT `id` FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    public function findIdListByExample( PEEP_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT `id` FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumnList($sql, array(), $cacheLifeTime, $tags);
    }

    protected function clearCache()
    {
        $tagsToClear = $this->getClearCacheTags();

        if ( $tagsToClear )
        {
            PEEP::getCacheManager()->clean($tagsToClear);
        }
    }

    /**
     * @return array
     */
    protected function getClearCacheTags()
    {
        return array();
    }
    
    protected function tableDataChanged()
    {
        
    }
}
