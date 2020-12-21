<?php

class FRIENDS_BOL_Service
{
    const STATUS_ACTIVE = FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE;
    const STATUS_PENDING = FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING;
    const STATUS_IGNORED = FRIENDS_BOL_FriendshipDao::VAL_STATUS_IGNORED;

    /**
     * @var FRIENDS_BOL_FriendshipDao
     */
    private $friendshipDao;
    /**
     * Class instance
     *
     * @var FRIENDS_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        $this->friendshipDao = FRIENDS_BOL_FriendshipDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return FRIENDS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function saveFriendship(FRIENDS_BOL_Friendship $friendship)
    {
        $this->friendshipDao->save($friendship);

        return $friendship;
    }

    public function getUnreadFriendRequestsForUserIdList($userIdList)
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        return $this->friendshipDao->findUnreadFriendRequestsForUserIdList($userIdList);
    }

    /**
     * Save new friendship request
     *
     * @param integer $requesterId
     * @param integer $userId
     */
    public function request( $requesterId, $userId )
    {
        $this->friendshipDao->request($requesterId, $userId);
    }

    public function onRequest( $requesterId, $userId )
    {
        $event = new PEEP_Event('friends.request-sent', array(
            'senderId' => $requesterId,
            'recipientId' => $userId,
            'time' => time()
        ));

        PEEP::getEventManager()->trigger($event);
    }

    /**
     * Accept new friendship request
     *
     * @param integer $userId
     * @param integer $requesterId
     * @return FRIENDS_BOL_Friendship
     */
    public function accept( $userId, $requesterId )
    {
        return $this->friendshipDao->accept($userId, $requesterId);
    }

    public function onAccept( $userId, $requesterId, FRIENDS_BOL_Friendship $frendshipDto )
    {
        $se = BOL_UserService::getInstance();

        $names = $se->getDisplayNamesForList(array($requesterId, $userId));
        $uUrls = $se->getUserUrlsForList(array($requesterId, $userId));
        
        //Add Newsfeed activity action
        $event = new PEEP_Event('feed.action', array(
            'pluginKey' => 'friends',
            'entityType' => 'friend_add',
            'entityId' => $frendshipDto->id,
            'userId' => array($requesterId, $userId),
            'feedType' => 'user',
            'feedId' => $requesterId
        ), array(
            'string' => array("key" => 'friends+newsfeed_action_string', "vars" => array(
                'user_url' => $uUrls[$userId],
                'name' => $names[$userId],
                'requester_url' => $uUrls[$requesterId],
                'requester_name' => $names[$requesterId]
            ))
        ));
        PEEP::getEventManager()->trigger($event);

        //Send notification about accept of friendship request
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];

        $notificationParams = array(
            'pluginKey' => 'friends',
            'action' => 'friends-accept',
            'entityType' => 'friends-accept',
            'entityId' => $frendshipDto->id,
            'userId' => $requesterId,
            'time' => time()
        );

        $receiver = '<a href="'.$uUrls[$userId].'" target="_blank" >'.$names[$userId].'</a>';

        $notificationData = array(
            'string' => array(
                'key' => 'friends+notify_accept',
                'vars' => array(
                    'receiver' => $receiver
                )
            ),
            'avatar' => $avatar,
            'url' => $uUrls[$userId]
        );

        $event = new PEEP_Event('notifications.add', $notificationParams, $notificationData);
        PEEP::getEventManager()->trigger($event);

        $event = new PEEP_Event('friends.request-accepted', array(
            'senderId' => $requesterId,
            'recipientId' => PEEP::getUser()->getId(),
            'time' => time()
        ));

        PEEP::getEventManager()->trigger($event);
    }

    /**
     * Ignore new friendship request
     *
     * @param integer $requesterId
     * @param integer $userId
     */
    public function ignore( $requesterId, $userId )
    {
        $this->friendshipDao->ignore($requesterId, $userId);
    }

    /**
     * Cancel friendship
     *
     * @param integer $requesterId
     * @param integer $userId
     */
    public function cancel( $requesterId, $userId )
    {
        $this->friendshipDao->cancel($requesterId, $userId);
    }

    /**
     * Activate friendship
     *
     * @param integer $requesterId
     * @param integer $userId
     */
    public function activate( $requesterId, $userId )
    {
        $this->friendshipDao->activate($requesterId, $userId);
    }

    public function findFriendship( $userId, $user2Id )
    {
        return $this->friendshipDao->findFriendship($userId, $user2Id);
    }

    public function findFriendshipById( $friendshipId )
    {
        return $this->friendshipDao->findFriendshipById($friendshipId);
    }

    public function findFriendIdList( $userId, $first, $count, $type = 'friends' )
    {

        switch ( $type )
        {
            case 'friends':
                return $this->friendshipDao->findFriendIdList($userId, $first, $count);


            case 'sent-requests':
                return $this->friendshipDao->findRequestedUserIdList($userId, $first, $count);

            case 'got-requests':

                return $this->friendshipDao->findRequesterUserIdList($userId, $first, $count);
        }

        return array(array(), 0);
    }

    public function count( $userId = null, $friendId = null, $status = FRIENDS_BOL_Service::STATUS_ACTIVE, $orStatus = null, $viewed = null, $exclude = null )
    {
        return $this->friendshipDao->count($userId, $friendId, $status, $orStatus, $viewed, $exclude);
    }

    public function countFriends( $userId )
    {
        return $this->friendshipDao->findUserFriendsCount($userId);
    }

    public function deleteUserFriendships( $userId )
    {
        $this->friendshipDao->deleteUserFriendships($userId);
    }

    public function findAllActiveFriendships()
    {
        return $this->friendshipDao->findAllActiveFriendships();
    }

    public function findActiveFriendships( $first, $count )
    {
        return $this->friendshipDao->findActiveFriendships($first, $count);
    }
    /* -------------------- */

    public function findUserFriendsInList( $userId, $first, $count, $userIdList = null )
    {
        return $this->friendshipDao->findFriendIdList($userId, $first, $count, $userIdList);
    }

    public function findCountOfUserFriendsInList( $userId, $userIdList = null )
    {
        return $this->friendshipDao->findUserFriendsCount($userId, $userIdList);
    }

    public function findFriendshipListByUserId( $userId, $userIdList = array() )
    {
        return $this->friendshipDao->findFriendshipListByUserId($userId, $userIdList);
    }

    public function findRequestList( $userId, $beforeStamp, $offset, $count, $exclude = null )
    {
        return $this->friendshipDao->findRequestList($userId, $beforeStamp, $offset, $count, $exclude);
    }

    public function findNewRequestList( $userId, $afterStamp )
    {
        return $this->friendshipDao->findNewRequestList($userId, $afterStamp);
    }

    public function markViewedByIds( $idList, $viewed = true )
    {
        $this->friendshipDao->markViewedByIds($idList, $viewed);
    }

    public function markAllViewedByUserId( $userId, $viewed = true )
    {
        $this->friendshipDao->markAllViewedByUserId($userId, $viewed);
    }
}