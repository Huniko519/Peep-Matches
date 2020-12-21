<?php

final class BOL_VoteService
{
    /**
     * @var BOL_VoteDao
     */
    private $voteDao;
    /**
     * @var BOL_VoteService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_VoteService
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
     *
     */
    private function __construct()
    {
        $this->voteDao = BOL_VoteDao::getInstance();
    }

    /**
     * Saves and updates vote item.
     *
     * @param BOL_Vote $voteItem
     */
    public function saveVote( BOL_Vote $voteItem )
    {
        $this->voteDao->save($voteItem);
    }

    /**
     * Returns counted votes sum.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findTotalVotesResult( $entityId, $entityType )
    {
        return $this->voteDao->findTotalVote($entityId, $entityType);
    }

    /**
     * Returns counted votes sum for items list.
     *
     * @param array $entityIdList
     * @param string $entityType
     * @return array<integer>
     */
    public function findTotalVotesResultForList( $entityIdList, $entityType )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $arr = $this->voteDao->findTotalVoteForList($entityIdList, $entityType);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
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
        return $this->voteDao->findUserVote($entityId, $entityType, $userId);
    }

    /**
     * Returns vote item for user and items list.
     *
     * @param array $entityIds
     * @param string $entityType
     * @param integer $userId
     * @return array
     */
    public function findUserVoteForList( $entityIds, $entityType, $userId )
    {
        $list = $this->voteDao->findUserVoteForList($entityIds, $entityType, $userId);
        $res = array();
        foreach ( $list as $item )
        {
            if ( $item->vote > 0 )
            {
                $item->vote = "+1";
            }
            $res[$item->getEntityId()] = $item;
        }

        return $res;
    }

    /**
     * Deletes all votes for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemVotes( $entityId, $entityType )
    {
        $this->voteDao->deleteEntityItemVotes($entityId, $entityType);
    }

    public function findMostVotedEntityList( $entityType, $first, $count )
    {
        $arr = $this->voteDao->findMostVotedEntityList($entityType, $first, $count);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }

    public function findMostVotedEntityCount( $entityType )
    {
        return $this->voteDao->findMostVotedEntityCount($entityType);
    }

    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $status = $status ? 1 : 0;

        $this->voteDao->updateEntityStatus($entityType, $entityId, $status);
    }

    public function deleteUserVotes( $userId )
    {
        $this->voteDao->deleteUserVotes($userId);
    }

    public function delete( $vote )
    {
        $this->voteDao->delete($vote);
    }
    
    public function updateEntityItemStatus( $entityType, $entityId, $status = true )
    {
        $this->voteDao->updateEntityStatus($entityType, (int)$entityId, (int)$status);
    }

    /**
     * @param string $entityType
     */
    public function deleteByEntityType( $entityType )
    {
        $this->voteDao->deleteByEntityType($entityType);
    }
}