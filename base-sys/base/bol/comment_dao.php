<?php

class BOL_CommentDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const COMMENT_ENTITY_ID = 'commentEntityId';
    const MESSAGE = 'message';
    const CREATE_STAMP = 'createStamp';

    /**
     * Singleton instance.
     *
     * @var BOL_CommentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Comment';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_comment';
    }

    /**
     * Finds comment list for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findCommentList( $entityType, $entityId, $first, $count )
    {
        $query = "SELECT `c`.* FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId, 'first' => $first, 'count' => $count));
    }

    /**
     * Finds full comment list for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return array<BOL_Comment>
     */
    public function findFullCommentList( $entityType, $entityId )
    {
        $query = "SELECT `c`.* FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			ORDER BY `" . self::CREATE_STAMP . "`";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Returns comments count for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return integer
     */
    public function findCommentCount( $entityType, $entityId )
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			";

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType, 'entityId' => $entityId));
    }

    public function findMostCommentedEntityList( $entityType, $first, $count )
    {
        $query = "SELECT `ce`.`entityId` AS `id`, COUNT(*) AS `commentCount` FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ACTIVE . "` = 1
			GROUP BY `" . BOL_CommentEntityDao::ENTITY_ID . "`
			ORDER BY `commentCount` DESC
			LIMIT :first, :count";

        return $this->dbo->queryForList($query, array('entityType' => $entityType, 'first' => $first, 'count' => $count));
    }

    public function findCommentCountForEntityList( $entityType, $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $query = "SELECT `ce`.`entityId` AS `id`, COUNT(*) AS `commentCount` FROM `" . $this->getTableName() . "` AS `c`
			INNER JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce`
				ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` IN  ( " . $this->dbo->mergeInClause($idList) . " )
			GROUP BY `" . BOL_CommentEntityDao::ENTITY_ID . "`";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function deleteByCommentEntityId( $id )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::COMMENT_ENTITY_ID, $id);

        $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteEntityTypeComments( $entityType )
    {
        $query = "DELETE `c` FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `e` ON( `c`.`" . self::COMMENT_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType";

        $this->dbo->query($query, array('entityType' => trim($entityType)));
    }

    public function deleteByPluginKey( $pluginKey )
    {
        $query = "DELETE `c` FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `e` ON( `c`.`" . self::COMMENT_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . BOL_CommentEntityDao::PLUGIN_KEY . "` = :pluginKey";

        $this->dbo->query($query, array('pluginKey' => trim($pluginKey)));
    }

    public function findBatchCommentsCount( array $entities )
    {
        $queryStr = '';
        $params = array();
        foreach ( $entities as $entity )
        {
            $queryStr .= " (`ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = ? AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = ? ) OR";
            $params[] = $entity['entityType'];
            $params[] = $entity['entityId'];
        }
        $queryStr = substr($queryStr, 0, -2);

        $query = "SELECT `ce`.`entityType`, `ce`.`entityId`, COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE " . $queryStr . " GROUP BY `ce`.`id`";

        return $this->dbo->queryForList($query, $params);
    }

    public function findBatchCommentsList( $entities )
    {
        if ( empty($entities) )
        {
            return array();
        }

        $queryParts = array();
        $queryParams = array();
        $genId = 1;
        foreach ( $entities as $entity )
        {
            $queryParts[] = " SELECT * FROM ( SELECT `c`.*, `ce`.`entityType`, `ce`.`entityId` FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = ? AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = ?
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT 0, ? ) AS `al" . $genId++ . "` ".PHP_EOL;
            $queryParams[] = $entity['entityType'];
            $queryParams[] = $entity['entityId'];
            $queryParams[] = (int)$entity['countOnPage'];
        }

        $query = implode(" UNION ALL ", $queryParts);

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $queryParams);
    }
}
