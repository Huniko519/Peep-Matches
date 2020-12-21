<?php

class CNEWS_BOL_ActionDao extends PEEP_BaseDao
{
    const CACHE_TIMESTAMP_PREFERENCE = 'cnews_generate_action_set_timestamp';
    const CACHE_TIMEOUT = 300; // 5 min
    const CACHE_LIFETIME = 86400;
    const CACHE_TAG_INDEX = 'cnews_index';
    const CACHE_TAG_USER = 'cnews_user';
    const CACHE_TAG_USER_PREFIX = 'cnews_user_';
    const CACHE_TAG_FEED = 'cnews_feed';
    const CACHE_TAG_FEED_PREFIX = 'cnews_feed_';
    const CACHE_TAG_ALL = 'cnews_all';

    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_ActionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_ActionDao
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
        return 'CNEWS_BOL_Action';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_action';
    }

    /**
     *
     * @param $entityType
     * @param $entityId
     * @return CNEWS_BOL_Action
     */
    public function findAction( $entityType, $entityId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    public function findByPluginKey( $pluginKey )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('pluginKey', $pluginKey);

        return $this->findListByExample($example);
    }

    public function setStatusByPluginKey( $pluginKey, $status )
    {
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $query = "UPDATE " . $this->getTableName() . " action
            INNER JOIN " . $activityDao->getTableName() . " activity ON action.id = activity.actionId
            SET activity.`status`=:s
            WHERE activity.activityType=:ca AND action.pluginKey=:pk";

        $this->dbo->query($query, array(
            's' => $status,
            'pk' => $pluginKey,
            'ca' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ));
    }

    public function findByFeed( $feedType, $feedId, $limit = null, $startTime = null, $formats = null )
    {
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $cacheStartTime = PEEP::getCacheManager()->load('cnews.feed_cache_time_' . $feedType . $feedId);
        if ( $cacheStartTime === null )
        {
            PEEP::getCacheManager()->save($startTime, 'cnews.feed_cache_time_' . $feedType . $feedId, array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_FEED,
                self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findByFeed"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId

            WHERE ' . $queryParts["where"] . '
                AND activity.status=:s
                AND activity.timeStamp<:st
                AND activity.privacy=:peb
                AND action_feed.feedType=:ft
                AND action_feed.feedId=:fi
                AND activity.visibility & :v

                AND cactivity.status=:s
                AND cactivity.activityType=:ac
                AND cactivity.privacy=:peb
                AND cactivity.visibility & :v

            GROUP BY action.id ORDER BY MAX(activity.timeStamp) DESC ' . $limitStr;

        $idList = $this->dbo->queryForColumnList($query, array(
            'ft' => $feedType,
            'fi' => $feedId,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'v' => CNEWS_BOL_Service::VISIBILITY_FEED,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_FEED,
            self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
        ));

        return $this->findOrderedListByIdList($idList);
    }

    public function findCountByFeed( $feedType, $feedId, $startTime = null, $formats = null )
    {
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findCountByFeed"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        /*$query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            ' . $queryParts["join"] . '

            LEFT JOIN ' . $activityDao->getTableName() . ' pactivity ON activity.actionId = pactivity.actionId
                AND (pactivity.status=:s AND pactivity.activityType=:ac AND pactivity.privacy!=:peb AND pactivity.visibility & :v)

            WHERE ' . $queryParts["where"] . ' AND pactivity.id IS NULL AND activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND action_feed.feedType=:ft AND action_feed.feedId=:fi AND activity.visibility & :v';
         * */
        
        $query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId

            WHERE ' . $queryParts["where"] . '
                AND activity.status=:s
                AND activity.timeStamp<:st
                AND activity.privacy=:peb
                AND action_feed.feedType=:ft
                AND action_feed.feedId=:fi
                AND activity.visibility & :v

                AND cactivity.status=:s
                AND cactivity.activityType=:ac
                AND cactivity.privacy=:peb
                AND cactivity.visibility & :v';

        return (int) $this->dbo->queryForColumn($query, array(
            'ft' => $feedType,
            'fi' => $feedId,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'v' => CNEWS_BOL_Service::VISIBILITY_FEED,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'st' => empty($startTime) ? time() : $startTime
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_FEED,
            self::CACHE_TAG_FEED_PREFIX . $feedType . $feedId
        ));
    }

    public function findByUser( $userId, $limit = null, $startTime = null, $formats = null )
    {
        $cacheKey = md5('user_feed' . $userId . ( empty($limit) ? '' : implode('', $limit) ) );

        $cachedIdList = PEEP::getCacheManager()->load($cacheKey);

        if ( $cachedIdList !== null )
        {
            $idList = json_decode($cachedIdList, true);

            return $this->findOrderedListByIdList($idList);
        }

        $followDao = CNEWS_BOL_FollowDao::getInstance();
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();
        $actionSetDao = CNEWS_BOL_ActionSetDao::getInstance();

        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $actionSetDao->deleteActionSetUserId($userId);
        $actionSetDao->generateActionSet($userId, $startTime);

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findByUser"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        $query = ' SELECT  b.`id` FROM
            ( SELECT  action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            LEFT JOIN ' . $followDao->getTableName() . ' follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND (
                    ( follow.userId=:u AND activity.visibility & :vf ) )

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND (
                        ( activity.userId=:u AND activity.visibility & :va ) )

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st
                AND ( ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) )

            UNION

            SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM ' . $this->getTableName() . ' action
                INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
                ' . $queryParts["join"] . '
                INNER JOIN ' . $activityDao->getTableName() . ' subscribe ON activity.actionId=subscribe.actionId and subscribe.activityType=:as AND subscribe.userId=:u
                WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st

                ) b

            GROUP BY b.`id` ORDER BY MAX(b.timeStamp) DESC ' . $limitStr;

        $idList = array_unique($this->dbo->queryForColumnList($query, array(
            'u' => $userId,
            'va' => CNEWS_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => CNEWS_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => CNEWS_BOL_Service::VISIBILITY_FEED,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        )));
        
        if ( $limit[0] == 0 )
        {
            $cacheLifeTime = self::CACHE_LIFETIME;
            $cacheTags = array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_USER,
                self::CACHE_TAG_USER_PREFIX . $userId
            );

            PEEP::getCacheManager()->save(json_encode($idList), $cacheKey, $cacheTags, $cacheLifeTime);
        }

        return $this->findOrderedListByIdList($idList);
    }

    public function findCountByUser( $userId, $startTime, $formats = null )
    {
        $cacheKey = md5('user_feed_count' . $userId );
        $cachedCount = PEEP::getCacheManager()->load($cacheKey);

        if ( $cachedCount !== null )
        {
            return $cachedCount;
        }

        $followDao = CNEWS_BOL_FollowDao::getInstance();
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();
        $actionSetDao = CNEWS_BOL_ActionSetDao::getInstance();

        /*$actionSetDao->deleteActionSetUserId($userId);
        $actionSetDao->generateActionSet($userId, $startTime);*/

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findCountByUser"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        $query = 'SELECT COUNT(DISTINCT `id`) FROM ( SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            LEFT JOIN ' . $followDao->getTableName() . ' follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND (
                    ( follow.userId=:u AND activity.visibility & :vf ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND (
                    ( activity.userId=:u AND activity.visibility & :va ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st
            AND ( ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) )

        UNION

        SELECT action.`id` FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN `' . $actionSetDao->getTableName() . '` cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            INNER JOIN ' . $activityDao->getTableName() . ' subscribe ON activity.actionId=subscribe.actionId and subscribe.activityType=:as AND subscribe.userId=:u
            WHERE ' . $queryParts["where"] . ' AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st ) a ';

        $count = $this->dbo->queryForColumn($query, array(
            'u' => $userId,
            'va' => CNEWS_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => CNEWS_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => CNEWS_BOL_Service::VISIBILITY_FEED,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        ));

        $cacheLifeTime = self::CACHE_LIFETIME;
        $cacheTags = array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_USER,
            self::CACHE_TAG_USER_PREFIX . $userId
        );

        PEEP::getCacheManager()->save($count, $cacheKey, $cacheTags, $cacheLifeTime);

        return $count;
    }

    public function findSiteFeed( $limit = null, $startTime = null, $formats = null )
    {
        $limitStr = '';
        if ( !empty($limit) )
        {
            $limitStr = "LIMIT " . intval($limit[0]) . ", " . intval($limit[1]);
        }

        $cacheStartTime = PEEP::getCacheManager()->load('cnews.site_cache_time');
        if ( $cacheStartTime === null )
        {
            PEEP::getCacheManager()->save($startTime, 'cnews.site_cache_time', array(
                self::CACHE_TAG_ALL,
                self::CACHE_TAG_INDEX,
            ), self::CACHE_LIFETIME);
        }
        else
        {
            $startTime = $cacheStartTime;
        }

        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("cactivity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findSiteFeedCount"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }
        
        $query = 'SELECT action.id FROM ' . $this->getTableName() . ' action
            INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
            INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON action.id = cactivity.actionId
            ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND
                (cactivity.status=:s AND cactivity.activityType=:ac AND cactivity.privacy=:peb AND cactivity.visibility & :v)
                AND
                (activity.status=:s AND activity.privacy=:peb AND activity.visibility & :v AND activity.timeStamp < :st)
              GROUP BY action.id
              ORDER BY MAX(activity.timeStamp) DESC ' . $limitStr;

        $idList = $this->dbo->queryForColumnList($query, array(
            'v' => CNEWS_BOL_Service::VISIBILITY_SITE,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_INDEX
        ));

        return $this->findOrderedListByIdList($idList);
    }

    private function findOrderedListByIdList( $idList )
    {
        if ( empty($idList) )
	    {
	          return array();
	    }
	    
        $unsortedDtoList = $this->findByIdList($idList);
        $unsortedList = array();
        foreach ( $unsortedDtoList as $dto )
        {
            $unsortedList[$dto->id] = $dto;
        }

        $sortedList = array();
        foreach ( $idList as $id )
        {
            if ( !empty($unsortedList[$id]) )
            {
            	$sortedList[] = $unsortedList[$id];
            }
        }

        return $sortedList;
    }

    public function findSiteFeedCount( $startTime = null, $formats = null )
    {
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "CNEWS_BOL_ActionDao::findSiteFeedCount"
        ));
        
        if ( $formats !== null )
        {
            $queryParts["where"] .= " AND action.format IN ( '" . implode("','", $formats) . "' )";
        }

        $query = 'SELECT COUNT(DISTINCT action.id) FROM ' . $this->getTableName() . ' action
                    INNER JOIN ' . $activityDao->getTableName() . ' activity ON action.id = activity.actionId
                    LEFT JOIN ' . $activityDao->getTableName() . ' pactivity ON activity.actionId = pactivity.actionId
                        AND (pactivity.status=:s AND pactivity.activityType=:ac AND pactivity.privacy!=:peb AND pactivity.visibility & :v)
                    ' . $queryParts["join"] . '

                    WHERE ' . $queryParts["where"] . ' AND pactivity.id IS NULL AND activity.status=:s AND activity.activityType=:ac AND activity.privacy=:peb AND activity.visibility & :v';

        return $this->dbo->queryForColumn($query, array(
            'v' => CNEWS_BOL_Service::VISIBILITY_SITE,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE
        ), self::CACHE_LIFETIME, array(
            self::CACHE_TAG_ALL,
            self::CACHE_TAG_INDEX
        ));
    }

    public function findListByUserId( $userId )
    {
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        $query = "SELECT DISTINCT action.* FROM " . $this->getTableName() . " action
            INNER JOIN " . $activityDao->getTableName() . " activity ON action.id=activity.actionId
            WHERE activity.activityType=:ca AND activity.userId=:u";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ca' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'u' => $userId
        ));
    }

    public function setPrivacyByEntityType( $userId, array $entityTypes, $privacy )
    {
        if ( empty($entityTypes) )
        {
            return;
        }

        $query = "UPDATE " . $this->getTableName() . " SET privacy=:p WHERE userId=:u AND entityType IN (" . $this->dbo->mergeInClause($entityTypes) . ")";

        $this->dbo->query($query, array(
            'u' => $userId,
            'p' => $privacy
        ));
    }

    /**
     *
     * @param $actionId
     * @return CNEWS_BOL_Action
     */
    public function findActionById( $actionId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('id', $actionId);

        return $this->findObjectByExample($example);
    }

    public function findExpiredIdList( $inactivePeriod, $count = null )
    {
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();
        $systemActivities = CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES;
        $limit = '';

        if ( !empty($count) )
        {
            $limit = ' LIMIT ' . $count;
        }

        $query = 'SELECT DISTINCT cactivity.actionId FROM ' . $activityDao->getTableName() . ' cactivity
            LEFT JOIN ' . $activityDao->getTableName() . ' activity
                    ON cactivity.actionId=activity.actionId AND activity.activityType NOT IN ("' . implode('", "', $systemActivities) . '")
                WHERE activity.id IS NULL AND cactivity.activityType=:c AND cactivity.timeStamp < :ts' . $limit;

        return $this->dbo->queryForColumnList($query, array(
            'c' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'ts' => time() - $inactivePeriod
        ));
    }
}