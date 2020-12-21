<?php

class NOTIFICATIONS_BOL_NotificationDao extends PEEP_BaseDao
{

    const VIEWED_NOTIFICATIONS_COUNT = 50;

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
     * @var NOTIFICATIONS_BOL_NotificationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_NotificationDao
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
        return 'NOTIFICATIONS_BOL_Notification';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'notifications_notification';
    }

    public function findNotificationList( $userId, $beforeStamp, $ignoreIds, $count )
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

    public function findNewNotificationList( $userId, $afterStamp = null )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('viewed', false);
        // TODO: uncomment
        if ( $afterStamp )
        {
            $example->andFieldGreaterThan('timeStamp', $afterStamp);
        }
        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findNotificationListForSend( $userIdList )
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

    public function findNotificationCount( $userId, $viewed = null, $exclude = null )
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

    public function findNotification( $entityType, $entityId, $userId )
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

    public function saveNotification( NOTIFICATIONS_BOL_Notification $notification )
    {
        if ( empty($notification->id) )
        {
            $dto = $this->findNotification($notification->entityType, $notification->entityId, $notification->userId);

            if ( $dto != null )
            {
                $notification->id = $dto->id;
            }
        }

        $this->save($notification);
    }

    public function deleteExpired()
    {
        $query = "SELECT userId FROM " . $this->getTableName() . " WHERE  viewed=1 GROUP BY userId HAVING count(id) > :ql LIMIT :limit";
        $userIdList = $this->dbo->queryForColumnList($query, array(
            'ql' => self::VIEWED_NOTIFICATIONS_COUNT,
            'limit' => 1000
        ));

        $expiredItemsIdList = array();

        foreach ( $userIdList as $userId )
        {
            $expiredItemsIdListByUser = $this->findExpiredNotificationsIdListByUserId($userId);
            $expiredItemsIdList = array_merge($expiredItemsIdList, $expiredItemsIdListByUser);

            if ( count($expiredItemsIdList) > 2000 )
            {
                $this->deleteByIdList($expiredItemsIdList);
                $expiredItemsIdList = array();
            }
        }

        if ( !empty($expiredItemsIdList) )
        {
            $this->deleteByIdList($expiredItemsIdList);
        }
    }

    public function findExpiredNotificationsIdListByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('viewed', 1);
        $example->setOrder('timeStamp DESC');
        $example->setLimitClause(self::VIEWED_NOTIFICATIONS_COUNT, 1000);

        return $this->findIdListByExample($example);
    }

    public function deleteNotification( $entityType, $entityId, $userId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }

    public function deleteNotificationByEntity( $entityType, $entityId )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }

    public function deleteNotificationByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();

        $example->andFieldEqual('pluginKey', $pluginKey);

        $this->deleteByExample($example);
    }

    public function setNotificationStatusByPluginKey( $pluginKey, $status )
    {
        $query = "UPDATE " . $this->getTableName() . " SET `active`=:s WHERE pluginKey=:pk";

        $this->dbo->query($query, array(
            's' => (int) $status,
            'pk' => $pluginKey
        ));
    }

}