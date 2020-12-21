<?php

class BOL_TagDao extends PEEP_BaseDao
{
    // table field names
    const LABEL = 'label';

    /**
     * Singleton instance.
     *
     * @var BOL_TagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_TagDao
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
        return 'BOL_Tag';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_tag';
    }

    /**
     * Returns dto list for provided tag labels.
     *
     * @param array<string>$labels
     * @return array
     */
    public function findTagsByLabel( $labels )
    {
        $example = new PEEP_Example();
        $example->andFieldInArray(self::LABEL, $labels);

        return $this->findListByExample($example);
    }

    public function findTagListByEntityIdList( $entityType, array $idList )
    {
        $query = "SELECT `t`.`label`, `et`.*  FROM " . $this->getTableName() . " AS `t`
			INNER JOIN `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et` ON ( `et`.`tagId` = `t`.`id` )
			WHERE `et`.`entityId` IN (" . $this->dbo->mergeInClause($idList) . ") AND `et`.`entityType` = :entityType";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    /**
     * Returns most popular tags for entity type.
     * 
     * @param string $entityType
     * @param integer $limit
     * @return array
     */
    public function findMostPopularTags( $entityType, $limit )
    {
        $query = "SELECT * FROM
	    		(
	    			SELECT `et`.*, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
					LEFT JOIN `" . $this->getTableName() . "` AS `t` ON ( `et`.`tagId` = `t`.`id`	)
					WHERE `et`.`entityType` = :entityType AND `et`.`active` = 1
					GROUP BY `tagId`
                                        ORDER BY `count` DESC
                                        LIMIT :limit
				) AS `t` 
				ORDER BY `t`.`label`";

        return $this->dbo->queryForList($query, array('limit' => $limit, 'entityType' => $entityType));
    }

    /**
     * Returns tag list with popularity for provided entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagsWithPopularity( $entityId, $entityType )
    {
        $query = "SELECT * FROM
	    		(
	    			SELECT `et`.*, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
					INNER JOIN `" . $this->getTableName() . "` AS `t`
					ON ( `et`.`tagId` = `t`.`id`)
					WHERE `et`.`entityId` = :entityId AND `et`.`entityType` = :entityType
					GROUP BY `tagId` ORDER BY `count` DESC
				) AS `t` 
				ORDER BY `t`.`label`";

        return $this->dbo->queryForList($query, array('entityId' => $entityId, 'entityType' => $entityType));
    }
}