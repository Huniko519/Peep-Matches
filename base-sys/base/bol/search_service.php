<?php

class BOL_SearchService
{
    const USER_LIST_SIZE = 500;

    const SEARCH_RESULT_ID_VARIABLE = "PEEP_SEARCH_RESULT_ID";

    /**
     * @var BOL_SearchDao
     */
    private $searchDao;
    /**
     * @var BOL_SearchResultDao
     */
    private $searchResultDao;
    /**
     * Singleton instance.
     *
     * @var BOL_SearchService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->searchDao = BOL_SearchDao::getInstance();
        $this->searchResultDao = BOL_SearchResultDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SearchService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * Save search Result. Returns search id.
     *
     * @param array $idList
     * @return int
     */
    public function saveSearchResult( array $idList )
    {
        $search = new BOL_Search();
        $search->timeStamp = time();

        $this->searchDao->save($search);

        $this->searchResultDao->saveSearchResult($search->id, $idList);

        $event = new PEEP_Event('base.after_save_search_result', array('searchDto' => $search, 'userIdList' => $idList), array());
        PEEP::getEventManager()->trigger($event);

        return $search->id;
    }

    /**
     * Return user id list
     *
     * @param int $listId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        return $this->searchResultDao->getUserIdList($listId, $first, $count, $excludeList);
    }

    public function countSearchResultItem( $listId )
    {
        return $this->searchResultDao->countSearchResultItem($listId);
    }

    public function deleteExpireSearchResult()
    {
        $list = $this->searchDao->findExpireSearchId();

        if ( !empty($list) )
        {
            $this->searchResultDao->deleteSearchResultItems($list);
            $this->searchDao->deleteByIdList($list);
        }
    }
}