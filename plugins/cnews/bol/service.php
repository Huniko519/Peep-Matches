<?php

class CNEWS_BOL_Service
{
    const VISIBILITY_SITE = 1;
    const VISIBILITY_FOLLOW = 2;
    const VISIBILITY_AUTHOR = 4;
    const VISIBILITY_FEED = 8;

    const VISIBILITY_FULL = 15;

    const ACTION_STATUS_ACTIVE = 'active';
    const ACTION_STATUS_INACTIVE = 'inactive';

    const PRIVACY_EVERYBODY = 'everybody';
    const PRIVACY_ACTION_VIEW_MY_FEED = 'view_my_feed';

    const SYSTEM_ACTIVITY_CREATE = 'create';
    const SYSTEM_ACTIVITY_SUBSCRIBE = 'subscribe';

    public $SYSTEM_ACTIVITIES = array(
        self::SYSTEM_ACTIVITY_CREATE,
        self::SYSTEM_ACTIVITY_SUBSCRIBE
    );
    
    const EVENT_BEFORE_ACTION_DELETE = "feed.before_action_delete";
    const EVENT_AFTER_ACTION_ADD = "feed.after_action_add";

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return CNEWS_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var CNEWS_BOL_ActionDao
     */
    private $actionDao;

    /**
     *
     * @var CNEWS_BOL_FollowDao
     */
    private $followDao;

    /**
     *
     * @var CNEWS_BOL_ActionFeedDao
     */
    private $actionFeedDao;

    /**
     *
     * @var CNEWS_BOL_LikeDao
     */
    private $likeDao;

    /**
     *
     * @var CNEWS_BOL_StatusDao
     */
    private $statusDao;

    /**
     *
     * @var CNEWS_BOL_ActivityDao
     */
    private $activityDao;

    /**
     *
     * @var CNEWS_BOL_UserActionDao
     */
    private $actionSetDao;

    /**
     *
     * @var CNEWS_BOL_CronCommandDao
     */
    private $cronCommandDao;

    private function __construct()
    {
        $this->actionDao = CNEWS_BOL_ActionDao::getInstance();
        $this->actionFeedDao = CNEWS_BOL_ActionFeedDao::getInstance();
        $this->followDao = CNEWS_BOL_FollowDao::getInstance();
        $this->likeDao = CNEWS_BOL_LikeDao::getInstance();
        $this->statusDao = CNEWS_BOL_StatusDao::getInstance();
        $this->activityDao = CNEWS_BOL_ActivityDao::getInstance();
        $this->cronCommandDao = CNEWS_BOL_CronCommandDao::getInstance();
        $this->actionSetDao = CNEWS_BOL_ActionSetDao::getInstance();
    }

    public function saveAction( CNEWS_BOL_Action $action )
    {
        $this->actionDao->save($action);

        return $action;
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @return CNEWS_BOL_Action
     */
    public function findAction( $entityType, $entityId )
    {
        $dto = $this->actionDao->findAction($entityType, $entityId);

        return $dto;
    }

    /**
     *
     * @param int $actionId
     * @return CNEWS_BOL_Action
     */
    public function findActionById( $actionId )
    {
        $dto = $this->actionDao->findById($actionId);

        return $dto;
    }

    public function removeAction( $entityType, $entityId )
    {
        $dto = $this->actionDao->findAction($entityType, $entityId);

        if ( $dto === null )
        {
            return;
        }
        
        $event = new PEEP_Event(self::EVENT_BEFORE_ACTION_DELETE, array(
            "actionId" => $dto->id,
            "entityType" => $dto->entityType,
            "entityId" => $dto->entityId
        ));
        PEEP::getEventManager()->trigger($event);

        $this->likeDao->deleteByEntity($dto->entityType, $dto->entityId);
        $this->actionDao->delete($dto);

        $activityIds = $this->activityDao->findIdListByActionIds(array($dto->id));
        $this->actionFeedDao->deleteByActivityIds($activityIds);
        $this->activityDao->deleteByIdList($activityIds);

        $commentEntity = BOL_CommentService::getInstance()->findCommentEntity($dto->entityType, $dto->entityId);

        if ( !empty($commentEntity) && $commentEntity->pluginKey == 'cnews' )
        {
            BOL_CommentService::getInstance()->deleteEntityComments($commentEntity->entityType, $commentEntity->entityId);
            BOL_CommentService::getInstance()->deleteCommentEntity($commentEntity->id);
        }

        $actionData = json_decode($dto->data, true);

        // delete attachments
        if( !empty($actionData['attachmentId']) )
        {
            BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("cnews", $actionData['attachmentId']);
        }
    }

    public function removeActionById( $id )
    {
        /* @var $dto CNEWS_BOL_Action */
        $dto = $this->actionDao->findById($id);

        if ( $dto === null  )
        {
            return;
        }

        $this->removeAction($dto->entityType, $dto->entityId);
    }

    public function removeActionListByPluginKey( $pluginKey )
    {
        $list = $this->actionDao->findByPluginKey($pluginKey);

        foreach ( $list as $dto )
        {
            /* @var $dto CNEWS_BOL_Action */
            $this->removeAction($dto->entityType, $dto->entityId);
        }
    }

    public function findExpiredActions( $inactivePeriod )
    {
        $this->actionDao->findExpired($inactivePeriod);
    }

    public function setActionStatusByPluginKey( $pluginKey, $status )
    {
        $this->actionDao->setStatusByPluginKey($pluginKey, $status);
    }


    // Activity

    public function saveActivity( CNEWS_BOL_Activity $activity )
    {
        $this->activityDao->saveOrUpdate($activity);

        return $activity;
    }

    public function addActivityToFeed( CNEWS_BOL_Activity $activity, $feedType, $feedId )
    {
        $actionFeed = new CNEWS_BOL_ActionFeed();
        $actionFeed->activityId = (int) $activity->id;
        $actionFeed->feedType = trim($feedType);
        $actionFeed->feedId = (int) $feedId;

        $this->actionFeedDao->addIfNotExists($actionFeed);

        return $actionFeed;
    }

    public function deleteActivityFromFeed( $activityId, $feedType, $feedId )
    {
        $this->actionFeedDao->deleteByFeedAndActivityId($feedType, $feedId, $activityId);
    }
    
    public function findFeedListByActivityids( $activityIds )
    {
        $list = $this->actionFeedDao->findByActivityIds($activityIds);
        $out = array();
        foreach ( $list as $af )
        {
            $out[$af->activityId] = isset($out[$af->activityId]) 
                    ? $out[$af->activityId] : array();
            
            $out[$af->activityId][] = $af;
        }
        
        return $out;
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
        return $this->activityDao->findActivityItem($activityType, $activityId, $actionId);
    }

    private function processActivityKey( $activityKey, $context = null )
    {
        $params = array();
        $keys = array();

        $_keys = is_array($activityKey) ? $activityKey : explode(',', $activityKey);
        foreach ( $_keys as $key )
        {
            $_key = is_array($key) ? $key : explode(',', $key);
            $keys = array_merge($keys, $_key);
        }

        foreach ( $keys as $key )
        {
            $params[] = $this->parseActivityKey($key, $context);
        }

        return $params;
    }

    private function parseActivityKey( $key, $context = null )
    {
        $key = str_replace('*', '', $key);

        $temp = explode(':', $key);

        $userId = empty($temp[2]) ? null : $temp[2];
        $actionKey = empty($temp[1]) ? null : $temp[1];
        $activityKey = empty($temp[0]) ? null : $temp[0];

        $out = array(
            'action' => array( 'entityType' => null, 'entityId' => null, 'id' => null ),
            'activity' => array( 'activityType' => null, 'activityId' => null, 'id' => null, 'userId' => $userId)
        );

        if ( is_numeric($actionKey) && strpos($actionKey, '.') === false )
        {
            $out['action']['id'] = $actionKey;
        }
        else
        {
            $temp = explode('.', $actionKey);

            $out['action']['entityType'] = $temp[0];
            $out['action']['entityId'] = empty($temp[1]) ? null : $temp[1];

        }

        if ( is_numeric($activityKey) && strpos($activityKey, '.') === false )
        {
            $out['activity']['id'] = $activityKey;
        }
        else
        {
            $temp = explode('.', $activityKey);
            $out['activity']['activityType'] = empty($temp[0]) ? null : $temp[0];
            $out['activity']['activityId'] = empty($temp[1]) ? null : $temp[1];
        }

        if ( !empty($context) )
        {
            $context = $this->parseActivityKey( $context );
            foreach ( $context as $k => $c )
            {
                $out[$k] = array_merge($c, array_filter($out[$k]));
            }
        }

        return $out;
    }

    public function testActivityKey( $key, $testKey, $all = false )
    {
        $key = $this->parseActivityKey($key);
        $testKey= $this->processActivityKey($testKey);

        $result = true;
        foreach ( $testKey as $tk )
        {
            $result = true;
            foreach ( $tk as $type => $f )
            {
                foreach ( $f as $k => $v )
                {
                    $r = empty($key[$type][$k]) ? true : empty($v) || $key[$type][$k] == $v;
                    if ( !$r )
                    {
                        $result = false;

                        break 2;
                    }
                }
            }

            if ( $result && !$all || !$result && $all)
            {
                break;
            }
        }

        return $result;
    }

    /**
     * Find activity by special key
     *
     * [activityType].[activityId]:[entityType].[entityId]:[userId]
     * 
     * @param $activityKey
     * @return array
     */
    public function findActivity( $activityKey, $context = null )
    {
        $params = $this->processActivityKey($activityKey, $context);

        return $this->activityDao->findActivity($params);
    }

    public function updateActivity( $activityKey, $updateFields, $context = null )
    {
        if ( empty($updateFields) )
        {
            return;
        }

        $params = $this->processActivityKey($activityKey, $context);

        return $this->activityDao->updateActivity($params, $updateFields);
    }

    public function removeActivity( $activityKey, $context = null )
    {
        $params = $this->processActivityKey($activityKey, $context);

        $this->activityDao->deleteActivity($params);
    }

    public function setActivityPrivacy( $activityKeys, $privacy, $userId )
    {
        $this->updateActivity($activityKeys, array('privacy' => $privacy), '*:*:' . $userId);
    }


    //Follow

    public function isFollow( $userId, $feedType, $feedId, $permission = self::PRIVACY_EVERYBODY )
    {
        return $this->followDao->findFollow($userId, $feedType, $feedId, $permission) !== null;
    }

    public function findFollowByFeedList( $userId, $feedList, $permission = self::PRIVACY_EVERYBODY )
    {
        $follows = $this->followDao->findFollowByFeedList($userId, $feedList, $permission);

        $out = array();
        foreach ( $follows as $follow )
        {
            $out[$follow->feedType . $follow->feedId] = $follow;
        }

        return $out;
    }

    public function isFollowList( $userId, $feedList, $permission = self::PRIVACY_EVERYBODY )
    {
        $follows = $this->findFollowByFeedList($userId, $feedList, $permission);

        $out = array();
        foreach ( $feedList as $feed )
        {
            if ( !isset($out[$feed["feedType"]]) )
            {
                $out[$feed["feedType"]] = array();
            }

            $out[$feed["feedType"]][$feed["feedId"]] = !empty($follows[$feed["feedType"].$feed["feedId"]]);
        }

        return $out;
    }

    public function findFollowList( $feedType, $feedId, $permission = null )
    {
        return $this->followDao->findList($feedType, $feedId, $permission);
    }

    public function addFollow( $userId, $feedType, $feedId, $permission = self::PRIVACY_EVERYBODY )
    {
        return $this->followDao->addFollow($userId, $feedType, $feedId, $permission);
    }

    public function removeFollow( $userId, $feedType, $feedId, $permission = null )
    {
        return $this->followDao->removeFollow($userId, $feedType, $feedId, $permission);
    }

    public function isLiked( $userId, $entityType, $entityId )
    {
        return $this->likeDao->findLike($userId, $entityType, $entityId) !== null;
    }

    public function findEntityLikesCount( $entityType, $entityId )
    {
        return $this->likeDao->findCountByEntity($entityType, $entityId);
    }

    public function findUserLikes( $userId )
    {
        return $this->likeDao->findByUserId($userId);
    }

    public function findEntityLikes( $entityType, $entityId )
    {
        return $this->likeDao->findByEntity($entityType, $entityId);
    }

    public function findLikesByEntityList( $entityList )
    {
        $list = $this->likeDao->findByEntityList($entityList);

        $out = array();
        foreach ( $list as $likeDto )
        {
            $out[$likeDto->entityType][$likeDto->entityId][] = $likeDto;
        }

        return $out;
    }

    public function findEntityLikeUserIds( $entityType, $entityId )
    {
        $likes = $this->findEntityLikes($entityType, $entityId);
        $out = array();

        foreach ( $likes as $like )
        {
            /* @var $like CNEWS_BOL_Like */
            $out[] = $like->userId;
        }

        return $out;
    }

    public function addLike( $userId, $entityType, $entityId )
    {
        return $this->likeDao->addLike($userId, $entityType, $entityId);
    }

    public function removeLike( $userId, $entityType, $entityId )
    {
        return $this->likeDao->removeLike($userId, $entityType, $entityId);
    }

    public function removeLikesByUserId( $userId )
    {
        $this->likeDao->removeLikesByUserId($userId);
    }

    public function removeActivityByUserId( $userId )
    {
        $this->activityDao->deleteByUserId($userId);
    }

    public function addStatus( $userId, $feedType, $feedId, $visibility, $status, $data = array() )
    {
        $statusDto = CNEWS_BOL_Service::getInstance()->saveStatus($feedType, $feedId, $status);

        $data["statusId"] = (int) $statusDto->id;
        $data["status"] = $status;
        
        $event = new PEEP_Event('feed.after_status_update', array(
            'feedType' => $feedType,
            'feedId' =>  $feedId,
            'visibility' => (int) $visibility,
            'userId' => $userId
        ), $data);

        PEEP::getEventManager()->trigger($event);

        return array(
            'entityType' => $feedType . '-status',
            'entityId' => $statusDto->id
        );
    }
    
    public function saveStatus( $feedType, $feedId, $status )
    {
        return $this->statusDao->saveStatus($feedType, $feedId, $status);
    }

    public function getStatus( $feedType, $feedId )
    {
        $dto = $this->findStatusDto( $feedType, $feedId );

        if ( $dto === null )
        {
            return null;
        }

        return $dto->status;
    }

    /**
     *
     * @param $feedType
     * @param $feedId
     * @return CNEWS_BOL_Status
     */
    public function findStatusDto( $feedType, $feedId )
    {
        return $this->statusDao->findStatus( $feedType, $feedId );
    }

    /**
     *
     * @param $feedType
     * @param $feedId
     * @return CNEWS_BOL_Status
     */
    public function findStatusDtoById( $statusId )
    {
        return $this->statusDao->findById($statusId);
    }

    public function removeStatus( $feedType, $feedId )
    {
        $this->statusDao->removeStatus($feedType, $feedId);
    }

    public function findActionsByUserId( $userId )
    {
        return $this->actionDao->findListByUserId($userId);
    }


    //CronCommands

    public function findCronCommands()
    {
        return $this->cronCommandDao->findAll();
    }

    public function addCronCommand( $command, array $params = array() )
    {
        $commandDto = new CNEWS_BOL_CronCommand();
        $commandDto->command = $command;
        $commandDto->data = json_encode($params);
        $commandDto->processData = json_encode(array());
        $commandDto->timeStamp = time();

        $this->saveCronCommand($commandDto);

        return $commandDto;
    }

    public function saveCronCommand( CNEWS_BOL_CronCommand $command )
    {
        $this->cronCommandDao->save($command);
    }

    public function deleteCronCommands( $commandIds )
    {
        $this->cronCommandDao->deleteByIdList($commandIds);
    }

    //Privacy

    private $privacy = array();

    public function collectPrivacy()
    {
        $event = new BASE_CLASS_EventCollector('feed.collect_privacy');
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $item )
        {
            $key = $item[0];
            $privacyAction = $item[1];
            $this->privacy[$privacyAction][] = $key;
        }
    }

    public function getActivityKeysByPrivacyAction( $privacyAction )
    {
        return empty($this->privacy[$privacyAction]) ? array() : $this->privacy[$privacyAction];
    }

    public function getPrivacyActionByActivityKey( $activityKey )
    {
        foreach ( $this->privacy as $action => $keys )
        {
            if ( $this->testActivityKey($activityKey, $keys) )
            {
                return $action;
            }
        }

        return null;
    }

    /**
     * use only for cron jobs
     *
     * @param int $timestamp
     */

    public function deleteActionSetByTimestamp($timestamp)
    {
        $this->actionSetDao->deleteActionSetByTimestamp($timestamp);
    }

    public function deleteActionSetByUserId($userId)
    {
        $this->actionSetDao->deleteActionSetUserId($userId);
        BOL_PreferenceService::getInstance()->savePreferenceValue(CNEWS_BOL_ActionDao::CACHE_TIMESTAMP_PREFERENCE, 0, $userId);
    }

    public function clearUserFeedCahce( $userId )
    {
        //BOL_PreferenceService::getInstance()->savePreferenceValue(CNEWS_BOL_ActionDao::CACHE_TIMESTAMP_PREFERENCE, 0, $userId);

        $this->clearCache();
    }

    public function clearCache()
    {
        PEEP::getCacheManager()->clean(array(
            CNEWS_BOL_ActionDao::CACHE_TAG_ALL,
            CNEWS_BOL_ActionDao::CACHE_TAG_INDEX,
            CNEWS_BOL_ActionDao::CACHE_TAG_USER,
            CNEWS_BOL_ActionDao::CACHE_TAG_FEED
        ));
    }

    public function getActionPermalink( $actionId, $feedType = null, $feedId = null )
    {
        $url = PEEP::getRouter()->urlForRoute('cnews_view_item', array(
            'actionId' => $actionId
        ));

        return PEEP::getRequest()->buildUrlQueryString($url, array(
            'ft' => $feedType,
            'fi' => $feedId
        ));
    }

    public function markForDelete( $actionIdList )
    {
        $count = 100;
        $actionsForCommands = array_chunk($actionIdList, $count);

        foreach ( $actionsForCommands as $actionIds )
        {
            $this->addCronCommand('deleteActions', array(
                'actionIds' => $actionIds
            ));
        }
    }

    public function markExpiredForDelete()
    {
        $expirationPeriod = 3600 * 24 * 31 * 3; // Once in three month
        $expiredActionIds = $this->actionDao->findExpiredIdList($expirationPeriod);

        $this->markForDelete($expiredActionIds);
    }
}
