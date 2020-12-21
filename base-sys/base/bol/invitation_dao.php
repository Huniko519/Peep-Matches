<?php

class BOL_InvitationDao extends PEEP_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_InvitationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_InvitationDao
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
     * @see PEEP_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Invitation';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'base_invitation';
    }

    public function findInvitationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldLessOrEqual('timeStamp', $beforeStamp);

        if ( !empty($ignoreIds) )
        {
            $example->andFieldNotInArray('id', $ignoreIds);
        }

        $example->setLimitClause(0, $count);
        $example->setOrder('viewed, timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findNewInvitationList( $userId, $afterStamp = null )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('viewed', false);
        if ( $afterStamp )
        {
            $example->andFieldGreaterThan('timeStamp', $afterStamp);
        }

        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }

     public function findInvitationListForSend( $userIdList )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $example = new PEEP_Example();

        $example->andFieldInArray('userId', $userIdList);
        $example->andFieldEqual('viewed', 0);
        $example->andFieldEqual('sent', 0);

        return $this->findListByExample($example);
    }

    public function findEntityInvitationList( $entityType, $entityId, $offset = 0, $count = null )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldLessOrEqual('entityId', $entityId);

        if ( !empty($count) )
        {
            $example->setLimitClause($offset, $count);
        }

        $example->setOrder('viewed, timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findEntityInvitationCount( $entityType, $entityId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldLessOrEqual('entityId', $entityId);

        return $this->countByExample($example);
    }

    public function findInvitationCount( $userId, $viewed = null, $exclude = null )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);

        if ( $viewed !== null )
        {
            $example->andFieldEqual('viewed', (int) (bool) $viewed);
        }

        if ( $exclude )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        return $this->countByExample($example);
    }

    public function findInvitation( $entityType, $entityId, $userId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    public function markViewedByIds( array $ids, $viewed = true )
    {
        if ( empty($ids) )
        {
            return;
        }

        $in = implode(',', $ids);

        $query = "UPDATE " . $this->getTableName() . " SET `viewed`=:viewed WHERE id IN ( " . $in . " )";

        $this->dbo->query($query, array(
            'viewed' => $viewed ? 1 : 0
        ));
    }

    public function markViewedByUserId( $userId, $viewed = true )
    {
        if ( !$userId )
        {
            return;
        }

        $query = "UPDATE " . $this->getTableName() . " SET `viewed` = :viewed WHERE userId = :userId";

        $this->dbo->query($query, array('viewed' => $viewed ? 1 : 0, 'userId' => $userId));
    }

    public function markSentByIds( array $ids, $sent = true )
    {
        if ( empty($ids) )
        {
            return;
        }

        $in = implode(',', $ids);

        $query = "UPDATE " . $this->getTableName() . " SET `sent`=:sent WHERE id IN ( " . $in . " )";

        $this->dbo->query($query, array(
            'sent' => $sent ? 1 : 0
        ));
    }

    public function saveInvitation( BOL_Invitation $invitation )
    {
        if ( empty($invitation->id) )
        {
            $dto = $this->findInvitation($invitation->entityType, $invitation->entityId, $invitation->userId);

            if ( $dto != null )
            {
                $invitation->id = $dto->id;
            }
        }

        $this->save($invitation);
    }

    public function deleteInvitation( $entityType, $entityId, $userId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }

    public function deleteInvitationByEntity( $entityType, $entityId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }

    public function deleteInvitationByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('pluginKey', $pluginKey);

        $this->deleteByExample($example);
    }

    public function setInvitationStatusByPluginKey( $pluginKey, $status )
    {
        $query = "UPDATE " . $this->getTableName() . " SET `active`=:s WHERE pluginKey=:pk";

        $this->dbo->query($query, array(
            's' => (int) $status,
            'pk' => $pluginKey
        ));
    }
}