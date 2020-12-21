<?php

class BOL_VoteDao extends PEEP_BaseDao
{
    const USER_ID = 'userId';
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const VOTE = 'vote';
    const TIME_STAMP = 'timeStamp';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_VoteDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_VoteDao
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
        return 'BOL_Vote';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_vote';
    }

    /**
     * Returns vote item for user.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Vote
     */
    public function findUserVote( $entityId, $entityType, $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns vote item for user and items list.
     * 
     * @param array $entityIdList
     * @param string $entityType
     * @param integer $userId
     * @return array
     */
    public function findUserVoteForList( $entityIdList, $entityType, $userId )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $example = new PEEP_Example();

        $example->andFieldInArray(self::ENTITY_ID, $entityIdList);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findListByExample($example);
    }

    /**
     * Returns counted votes sum.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findTotalVote( $entityId, $entityType )
    {
        $query = "
			SELECT 
				SUM(`" . self::VOTE . "`) AS `sum`,
				COUNT(if(`" . self::VOTE . "`>0, `" . self::VOTE . "`, NULL)) AS `up`,
				COUNT(if(`" . self::VOTE . "`<0, `" . self::VOTE . "`,NULL)) AS `down`
			FROM `" . $this->getTableName() . "`
			WHERE `" . self::ENTITY_ID . "` = :entityId AND `" . self::ENTITY_TYPE . "` = :entityType";

        return $this->dbo->queryForRow($query, array('entityId' => $entityId, 'entityType' => $entityType));
    }

    /**
     * Returns counted votes sum for items list.
     * 
     * @param array $entityIdList
     * @param string $entityType
     * @return array
     */
    public function findTotalVoteForList( $entityIdList, $entityType )
    {
        $query = "
	    SELECT `" . self::ENTITY_ID . "` AS `id`, SUM(`" . self::VOTE . "`) AS `sum`, COUNT(*) AS `count`,
            count(if(`vote` > 0, 1, NULL)) as up,
	    	count(if(`vote` < 0, 1, NULL)) as down
	    FROM `" . $this->getTableName() . "`
	    WHERE `" . self::ENTITY_ID . "` IN (" . $this->dbo->mergeInClause($entityIdList) . ") AND `" . self::ENTITY_TYPE . "` = :entityType
	    GROUP BY `" . self::ENTITY_ID . "`";
        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function findMostVotedEntityList( $entityType, $first, $count )
    {
        $query = "SELECT `" . self::ENTITY_ID . "` AS `id`, COUNT(*) as `count`, SUM(`" . self::VOTE . "`) AS `sum`
			FROM " . $this->getTableName() . "
                        WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1
			GROUP BY `" . self::ENTITY_ID . "`
                        ORDER BY `sum` DESC
                        LIMIT :first, :count";

        return $this->dbo->queryForList($query, array('entityType' => $entityType, 'first' => $first, 'count' => $count));
    }

    public function findMostVotedEntityCount( $entityType )
    {
        $query = "SELECT COUNT(DISTINCT `" . self::ENTITY_ID . "`) from `" . $this->getTableName() . "` WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1";

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType));
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Deletes all votes for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemVotes( $entityId, $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        $this->deleteByExample($example);
    }

    public function deleteUserVotes( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($entityType);
    }

    /**
     * Gets all votes for provided entity type and list of entity id
     * 
     * @param array<int> $idList
     * @param string $entityType
     * @return array<BOL_Vote>
     */
    public function getEntityTypeVotes( array $idList, $entityType )
    {
        if ( empty($idList) || empty($entityType) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldEqual(BOL_VoteDao::ENTITY_TYPE, $entityType);
        $example->andFieldInArray(BOL_VoteDao::ENTITY_ID, $idList);
        $example->andFieldEqual(BOL_VoteDao::ACTIVE, 1);
        return BOL_VoteDao::getInstance()->findListByExample($example);
    }
}
