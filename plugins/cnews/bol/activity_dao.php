<?php

class CNEWS_BOL_ActivityDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_ActivityDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_ActivityDao
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
        return 'CNEWS_BOL_Activity';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_activity';
    }

    public function deleteByActionIds( $actionIds )
    {
        if ( empty($actionIds) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('actionId', $actionIds);

        return $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    public function findIdListByActionIds( $actionIds )
    {
        if ( empty($actionIds) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('actionId', $actionIds);

        return $this->findIdListByExample($example);
    }

    public function findByActionIds( $actionIds )
    {
        if ( empty($actionIds) )
        {
            return array();
        }

        $example = new PEEP_Example();
        $example->andFieldInArray('actionId', $actionIds);

        return $this->findListByExample($example);
    }

    private function getQueryParts( $conts )
    {
        $actionDao = CNEWS_BOL_ActionDao::getInstance();
        $or = array();
        $join = '';

        foreach ( $conts as $cond )
        {
            $action = array_filter($cond['action']);
            $activity = array_filter($cond['activity']);

            $where = array();

            if ( empty($activity['id']) )
            {
                if ( !empty($action['id']) )
                {
                    $activity['actionId'] = $action['id'];
                }
                else if ( !empty($action) )
                {
                    $join = 'INNER JOIN ' . $actionDao->getTableName() . ' action ON activity.actionId=action.id';

                    foreach ( $action as $k => $v )
                    {
                        $where[] = 'action.' . $k . "='" . $this->dbo->escapeString($v) . "'";
                    }
                }
            }

            foreach ( $activity as $k => $v )
            {
                $where[] = 'activity.' . $k . "='" . $this->dbo->escapeString($v) . "'";
            }

            $or[] = implode(' AND ', $where);
        }

        return array(
            'join' => $join,
            'where' => empty($or) ? '1' : '( ' . implode(' ) OR ( ', $or) . ' )'
        );
    }

    public function findActivity( $params )
    {
        $qp = $this->getQueryParts($params);

        $query = 'SELECT activity.* FROM ' . $this->getTableName() . ' activity ' . $qp['join'] . ' WHERE ' . $qp['where'];

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }

    public function deleteActivity( $params )
    {
        $qp = $this->getQueryParts($params);

        $query = 'DELETE activity FROM ' . $this->getTableName() . ' activity ' . $qp['join'] . ' WHERE ' . $qp['where'];

        return $this->dbo->query($query);
    }

    public function updateActivity( $params, $updateFields )
    {
        if ( empty($updateFields) )
        {
            return;
        }

        $set = array();
        foreach ( $updateFields as $k => $v )
        {
            $set[] = 'activity.`' . $k . "`='" . $this->dbo->escapeString($v) . "'";
        }

        $qp = $this->getQueryParts($params);
        $query = 'UPDATE ' . $this->getTableName() . ' activity ' . $qp['join'] . ' SET ' . implode(', ', $set) . ' WHERE ' . $qp['where'];

        return $this->dbo->query($query);
    }

    /**
     *
     * @param string $activityType
     * @param int $activityId
     * @param int $actionId
     * @return CNEWS_BOL_Activity
     */
    public function findActivityItem( $activityType, $activityId, $actionId )
    {
        $example = new PEEP_Example();
        $example->andFieldEqual('activityType', $activityType);
        $example->andFieldEqual('activityId', $activityId);
        $example->andFieldEqual('actionId', $actionId);

        return $this->findObjectByExample($example);
    }

    public function findSiteFeedActivity( $actionIds )
    {
        $unionQueryList = array();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "CNEWS_BOL_ActivityDao::findSiteFeedActivity"
        ));
        
        $unionQueryList[] = 'SELECT activity.* FROM ' . $this->getTableName() . ' activity ' . $queryParts["join"] . '
            WHERE ' . $queryParts["where"] . ' AND activity.actionId IN(' . implode(', ', $actionIds) . ')
                AND activity.activityType IN ("' . implode('", "', CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) . '")';

        foreach ( $actionIds as $actionId )
        {
                $unionQueryList[] = 'SELECT a.* FROM (
                SELECT activity.* FROM ' . $this->getTableName() . ' activity ' . $queryParts["join"] . ' WHERE ' . $queryParts["where"] . ' AND  activity.actionId = ' . $actionId . ' AND activity.status=:s AND activity.privacy=:peb AND activity.visibility & :v ORDER BY activity.timeStamp DESC, activity.id DESC LIMIT 100
                        ) a';
        }

        $query = implode( ' UNION ', $unionQueryList ) . " ORDER BY 7 DESC, 1 DESC";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'v' => CNEWS_BOL_Service::VISIBILITY_SITE,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY
        ));
    }

    public function findUserFeedActivity( $userId, $actionIds )
    {
        $followDao = CNEWS_BOL_FollowDao::getInstance();
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();

        $unionQueryList = array();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "CNEWS_BOL_ActivityDao::findUserFeedActivity"
        ));
        
        $unionQueryList[] = 'SELECT activity.* FROM ' . $this->getTableName() . ' activity 
            WHERE activity.actionId IN(' . implode(', ', $actionIds) . ') 
            AND activity.activityType IN ("' . implode('", "', CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) . '")';

        foreach ( $actionIds as $actionId )
        {
            $unionQueryList[] = ' SELECT a.* FROM ( SELECT DISTINCT activity.* FROM ' . $this->getTableName() . ' activity
                ' . $queryParts["join"] . '
                
                LEFT JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
                LEFT JOIN ' . $followDao->getTableName() . ' follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
                WHERE ' . $queryParts["where"] . ' AND activity.actionId = ' . $actionId . ' AND
                (
                    (activity.status=:s AND
                    (
                        ( follow.userId=:u AND activity.visibility & :vf AND ( activity.privacy=:peb OR activity.privacy=follow.permission ) )
                        OR
                        ( activity.userId=:u AND activity.visibility & :va )
                        OR
                        ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed )
                    ))
                ) ORDER BY activity.timeStamp DESC, activity.id DESC LIMIT 100 ) a' ;
        }

        $query = implode( ' UNION ', $unionQueryList ) . " ORDER BY 7 DESC, 1 DESC";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => $userId,
            'va' => CNEWS_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => CNEWS_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => CNEWS_BOL_Service::VISIBILITY_FEED,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY
        ));
    }

    public function findFeedActivity( $feedType, $feedId, $actionIds )
    {
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();

        $unionQueryList = array();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("activity", "userId", array(
            "method" => "CNEWS_BOL_ActivityDao::findFeedActivity"
        ));
        
        $unionQueryList[] = 'SELECT activity.* FROM ' . $this->getTableName() . ' activity
            WHERE activity.actionId IN(' . implode(', ', $actionIds) . ')
            AND activity.activityType IN ("' . implode('", "', CNEWS_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) . '")';

        foreach ( $actionIds as $actionId )
        {
            $unionQueryList[] = 'SELECT a.* FROM ( SELECT DISTINCT activity.* FROM ' . $this->getTableName() . ' activity
                ' . $queryParts["join"] . '
                INNER JOIN ' . $actionFeedDao->getTableName() . ' action_feed ON activity.id=action_feed.activityId
                WHERE ' . $queryParts["where"] . ' AND activity.actionId = ' . $actionId . ' AND
                    (
                        activity.status=:s
                        AND activity.privacy=:peb
                        AND action_feed.feedType=:ft
                        AND action_feed.feedId=:fi
                        AND activity.visibility & :v
                    )
                ORDER BY activity.timeStamp DESC, activity.id DESC LIMIT 100 ) a';
        }

        $query = implode( ' UNION ', $unionQueryList ) . " ORDER BY 7 DESC, 1 DESC ";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'ft' => $feedType,
            'fi' => $feedId,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'v' => CNEWS_BOL_Service::VISIBILITY_FEED,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY
        ));
    }

    public function saveOrUpdate( CNEWS_BOL_Activity $activity )
    {
        $dto = $this->findActivityItem($activity->activityType, $activity->activityId, $activity->actionId);
        if ( $dto !== null )
        {
            $activity->id = $dto->id;
        }
        
        $this->save($activity);
    }

    public function batchSaveOrUpdate( array $dtoList )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $dtoList);
    }
}