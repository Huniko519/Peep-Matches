<?php

class BOL_SearchEntityTagDao extends PEEP_BaseDao
{
    /**
     * Entity tag
     */
    const ENTITY_TAG = 'entityTag';

    /**
     * Search entity id
     */
    const ENTITY_SEARCH_ID = 'searchEntityId';

    /**
     * Singleton instance.
     *
     * @var BOL_SearchEntityTagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SearchEntityTagDao
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
        return 'BOL_SearchEntityTag';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_search_entity_tag';
    }

    /**
     * Finds tags
     *
     * @param int $entitySearchId
     * @return PEEP_Entity
     */
    public function findTags( $entitySearchId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual(self::ENTITY_SEARCH_ID, $entitySearchId);

        return $this->findListByExample($example);
    }

    /**
     * Optimize table
     * 
     * @return void
     */
    public function optimizeTable()
    {
        $this->dbo->query('OPTIMIZE TABLE ' . $this->getTableName());
    }
}