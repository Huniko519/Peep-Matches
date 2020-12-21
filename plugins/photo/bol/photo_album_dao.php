<?php

class PHOTO_BOL_PhotoAlbumDao extends PEEP_BaseDao
{
    CONST NAME = 'name';
    CONST USER_ID = 'userId';
    CONST CREATE_DATETIME = 'createDatetime';

    protected function __construct()
    {
        parent::__construct();
    }

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
        return 'PHOTO_BOL_PhotoAlbum';
    }

    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'photo_album';
    }

    public function countAlbums( $userId, $exclude, $excludeEmpty = false )
    {
        if ( !$userId )
        {
            return false;
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countAlbums',
            array('album' => 'a', 'photo' => 'p'),
            array(
                'userId' => $userId,
                'exclude' => $exclude,
                'excludeEmpty' => $excludeEmpty
            )
        );

        $sql = 'SELECT COUNT(DISTINCT `a`.`id`)
            FROM `' . $this->getTableName() . '` AS `a`
                ' . $condition['join'] . '
                ' . ($excludeEmpty ? 'INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`a`.`id` = `p`.`albumId`)' : '') . '
            WHERE `a`.`userId` = :userId AND
            ' . $condition['where'] . ' AND
            ' . (!empty($exclude) ? '`a`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1');

        return $this->dbo->queryForColumn($sql, array_merge(
            array('userId' => $userId),
            $condition['params']
        ));
    }

    public function countEntityAlbums( $entityId, $entityType )
    {
        if ( !$entityId || !mb_strlen($entityType) )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->countByExample($example);
    }

    public function getUserAlbumList( $userId, $page, $limit, $exclude )
    {
        $first = ( $page - 1 ) * $limit;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getUserAlbumList',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'userId' => $userId,
                'page' => $page,
                'limit' => $limit,
                'exclude' => $exclude
            )
        );

        $sql = 'SELECT `a`.*
            FROM `%s` AS `a`
                INNER JOIN `%s` AS `p`
                    ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `a`.`userId` = :userId AND
                %s AND
                %s
            GROUP BY `a`.`id`
            LIMIT :first, :limit';
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoDao::getInstance()->getTableName(),
            $condition['join'],
            $condition['where'],
            !empty($exclude) ? '`a`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1'
        );

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            array(
                'userId' => $userId,
                'first' => (int) $first,
                'limit' => (int) $limit
            ),
            $condition['params']
        ));
    }
    
    public function findUserAlbumList( $userId, $first, $limit, array $exclude = array() )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findUserAlbumList',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'userId' => $userId,
                'first' => $first,
                'limit' => $limit,
                'exclude' => $exclude
            )
        );

        $sql = 'SELECT `a`.*
            FROM `' . $this->getTableName() . '` AS `a`
                INNER JOIN `' . PHOTO_BOL_PhotoDao::getInstance()->getTableName() . '` AS `p` ON(`p`.`albumId` = `a`.`id` AND `p`.`status` = :status)
                ' . $condition['join'] . '
            WHERE `a`.`' . self::USER_ID . '` = :userId ' .
                (count($exclude) !== 0 ? ' AND `a`.`id` NOT IN (' . implode(',', array_map('intval', $exclude)) . ')' : '') . ' AND
                ' . $condition['where'] . '
            GROUP BY `a`.`id`
            ORDER BY `a`.`id` DESC
            LIMIT :first, :limit';

        $params = array('userId' => $userId, 'status' => PHOTO_BOL_PhotoDao::STATUS_APPROVED, 'first' => (int)$first, 'limit' => (int)$limit);
        
        return $this->dbo->queryForList($sql, array_merge($params, $condition['params']));
    }

    public function getEntityAlbumList( $entityId, $entityType, $page, $limit )
    {
        $first = ( $page - 1 ) * $limit;

        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause($first, $limit);

        return $this->findListByExample($example);
    }

    public function getUserAlbums( $userId, $offset, $limit )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause($offset, $limit);

        return $this->findListByExample($example);
    }

    public function getEntityAlbums( $entityId, $entityType, $offset, $limit )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause($offset, $limit);

        return $this->findListByExample($example);
    }
    
    public function getUserAlbumIdList( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findIdListByExample($example);
    }
  
    public function getEntityAlbumIdList( $entityId, $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->findIdListByExample($example);
    }

    public function findAlbumByName( $name, $userId )
    {
        $name = trim($name);

        $userId = (int) $userId;

        $example = new PEEP_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    public function findEntityAlbumByName( $name, $entityId, $entityType )
    {
        $name = trim($name);

        $entityId = (int) $entityId;

        $example = new PEEP_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    public function suggestUserAlbums( $userId, $query )
    {
        if ( !$userId )
            return false;

        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setOrder('`name` ASC');

        if ( strlen($query) )
            $example->andFieldLike('name', $query . '%');

        $example->setLimitClause(0, 10);

        return $this->findListByExample($example);
    }
 
    public function suggestEntityAlbums( $entityType, $entityId, $query )
    {
        if ( !$entityId )
        {
            return false;
        }

        $example = new PEEP_Example();
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);
        $example->setOrder('`name` ASC');

        if ( strlen($query) )
            $example->andFieldLike('name', $query . '%');

        $example->setLimitClause(0, 10);

        return $this->findListByExample($example);
    }

    public function getAlbumsForDelete( $limit )
    {
        $example = new PEEP_Example();
        $example->setOrder('createDatetime ASC');
        $example->setLimitClause(0, (int)$limit);
        
        return $this->findIdListByExample($example);
    }
    
    public function findAlbumNameListByIdList( array $idList )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }
        
        $sql = 'SELECT `id`, `' . self::NAME . '`, `' . self::USER_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($idList))) . ')';
        
        $result = array();
        $resource = $this->dbo->queryForList($sql);
        
        foreach ( $resource as $row )
        {
            $result[$row['id']] = array('name' => $row[self::NAME], 'userId' => $row[self::USER_ID]);
        }
        
        return $result;
    }
    
    public function isAlbumOwner( $albumId, $userId )
    {
        if ( empty($albumId) || empty($userId) )
        {
            return FALSE;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `id` = :albumId AND `userId` = :userId';
        
        return (int)$this->dbo->queryForColumn($sql, array('albumId' => $albumId, 'userId' => $userId)) > 0;
    }
    
    public function findAlbumNameListByUserId( $userId, $excludeIdList = array() )
    {
        if ( empty($userId) )
        {
            return array();
        }
        
        $sql = 'SELECT `id`, `' . self::NAME . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId' . (count($excludeIdList) !== 0 ? ' AND `id` NOT IN (' . implode(',', array_map('intval', array_unique($excludeIdList))) . ')' : '') . '
            ORDER BY `' . self::NAME . '`';

        $result = array();
        $rows = $this->dbo->queryForList($sql, array('userId' => $userId));
        
        foreach ( $rows as $row )
        {
            $result[$row['id']] = $row[self::NAME];
        }

        return $result;
    }
}
