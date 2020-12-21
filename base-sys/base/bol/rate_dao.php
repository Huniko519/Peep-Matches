<?php

class BOL_RateDao extends PEEP_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const USER_ID = 'userId';
    const SCORE = 'score';
    const UPDATE_TIME_STAMP = 'timeStamp';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_RateDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RateDao
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
        return 'BOL_Rate';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_rate';
    }

    /**
     * Returns rate item for provided entity id, entity type and user id.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Rate
     */
    public function findRate( $entityId, $entityType, $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns entity item rate info.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityItemRateInfo( $entityId, $entityType )
    {
        return $this->dbo->queryForRow("SELECT COUNT(*) as `rates_count`, AVG(`score`) as `avg_score`
			FROM " . $this->getTableName() . " WHERE `entityId` = :entityId AND `entityType` = :entityType
			GROUP BY `entityId`", array('entityId' => $entityId, 'entityType' => $entityType));
    }

    /**
     * Returns rate info for list of entities.
     *
     * @param array $entityIds
     * @param string $entityType
     */
    public function findRateInfoForEntityList( $entityType, $entityIdList )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $query = "SELECT COUNT(*) as `rates_count`, AVG(`score`) as `avg_score`, `" . self::ENTITY_ID . "`
			FROM " . $this->getTableName() . " WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` IN (" . $this->dbo->mergeInClause($entityIdList) . ")
			GROUP BY `entityId`";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function findMostRatedEntityList( $entityType, $first, $count, $exclude )
    {
        $excludeCond = $exclude ? ' AND `' . self::ENTITY_ID . '` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        $query = "SELECT `" . self::ENTITY_ID . "` AS `id`, COUNT(*) as `ratesCount`, AVG(`score`) as `avgScore`
			FROM " . $this->getTableName() . "
                        WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1 " . $excludeCond . "
			GROUP BY `" . self::ENTITY_ID . "`
                        ORDER BY `avgScore` DESC, `ratesCount` DESC, MAX(`timeStamp`) DESC
                        LIMIT :first, :count";

        return $this->dbo->queryForList($query, array('entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count));
    }

    public function findMostRatedEntityCount( $entityType, $exclude )
    {
        $excludeCond = $exclude ? ' AND `' . self::ENTITY_ID . '` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        $query = "SELECT COUNT(DISTINCT `" . self::ENTITY_ID . "`) from `" . $this->getTableName() .
            "` WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1" . $excludeCond;

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType));
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Deletes rate entries for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemRates( $entityId, $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, (int) $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    /**
     * Deletes rate entries for provided params.
     *
     * @param $userId
     */
    public function deleteUserRates( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function findUserScore( $userId, $entityType, array $entityIdList )
    {
        if ( count($entityIdList) === 0 )
        {
            return array();
        }

        $sql = 'SELECT `' . self::ENTITY_ID . '`, `' . self::SCORE . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND
                `' . self::ENTITY_TYPE . '` = :entityType AND
                `' . self::ENTITY_ID . '` IN(' . implode(',', array_map('intval', array_unique($entityIdList))) . ')';

        return $this->dbo->queryForList($sql, array('userId' => $userId, 'entityType' => $entityType));
    }
}