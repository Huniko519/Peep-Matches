<?php

class CNEWS_BOL_ActionSetDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var CNEWS_BOL_ActionSetDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CNEWS_BOL_ActionSetDao
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
        return 'CNEWS_BOL_ActionSetDao';
    }

    /**
     * @see PEEP_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return PEEP_DB_PREFIX . 'cnews_action_set';
    }

    /**
     * @param int $userId
     * @param int $startTime
     */
    public function generateActionSet( $userId, $startTime = null )
    {
        $followDao = CNEWS_BOL_FollowDao::getInstance();
        $actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $activityDao = CNEWS_BOL_ActivityDao::getInstance();

        /*$query = ' REPLACE INTO '. $this->getTableName() . ' ( `actionId`, `userId`, `timestamp` )
                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                INNER JOIN ' . $followDao->getTableName() . ' sfollow ON saction_feed.feedId = sfollow.feedId AND saction_feed.feedType = sfollow.feedType
                WHERE sactivity.status=:s AND sactivity.activityType=:ac AND sactivity.timeStamp<:st AND
                        sfollow.userId=:u AND
                        ( sactivity.privacy=sfollow.permission OR sactivity.privacy=:peb)
                        AND sactivity.visibility & :vf

                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                WHERE sactivity.status=:s AND sactivity.activityType=:ac AND sactivity.timeStamp<:st AND
                        saction_feed.feedId=:u AND saction_feed.feedType="user" AND sactivity.visibility & :vfeed

                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st AND
                        ( sactivity.userId=:u AND sactivity.visibility & :va )';*/

        $query = ' REPLACE INTO '. $this->getTableName() . ' ( `actionId`, `userId`, `timestamp` )
                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                INNER JOIN ' . $followDao->getTableName() . ' sfollow ON saction_feed.feedId = sfollow.feedId AND saction_feed.feedType = sfollow.feedType
                WHERE sactivity.status=:s AND sactivity.activityType=:ac AND sactivity.timeStamp<:st AND
                        sfollow.userId=:u AND
                        ( sactivity.privacy=sfollow.permission OR sactivity.privacy=:peb)
                        AND sactivity.visibility & :vf
                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND cactivity.visibility & :va
                    AND sactivity.userId=:u
                    AND sactivity.visibility & :va
                    AND cactivity.status=:s

                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND sactivity.visibility & :vfeed
                    AND saction_feed.feedId=:u
                    AND saction_feed.feedType="user"
                    AND cactivity.status=:s';

        $this->dbo->update($query, array(
            'u' => (int)$userId,
            'va' => CNEWS_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => CNEWS_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => CNEWS_BOL_Service::VISIBILITY_FEED,
            's' => CNEWS_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => CNEWS_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => CNEWS_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        ));
    }

    /*
     * @param int $userId
     */
    public function deleteActionSetUserId( $userId )
    {
        $ex = new PEEP_Example();
        $ex->andFieldEqual('userId', (int)$userId);

        $this->deleteByExample($ex);
    }

    /**
     * @param int $startTime
     */
    public function deleteActionSetByTimestamp( $timestamp )
    {
        $ex = new PEEP_Example();
        $ex->andFieldLessOrEqual('timestamp', (int)$timestamp);

        $this->deleteByExample($ex);
    }
}