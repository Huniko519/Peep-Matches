<?php


class PROFILELIKE_BOL_ProfilelikeDao extends PEEP_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var PROFILELIKE_BOL_ProfilelikeDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return PROFILELIKE_BOL_ProfilelikeDao
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
     */
    public function getDtoClassName()
    {
        return 'PROFILELIKE_BOL_Profilelike';
    }
    
    /**
     * @see PEEP_BaseDao::getTableName()
     */
	 
	public function getTableName()
    {
        return PEEP_DB_PREFIX . 'profilelike';
    }
	
	public function getNotificationsTables()
	{
		return PEEP_DB_PREFIX . 'notifications_notification';
	}
	
	public function getProfileId()
	{
		$attrs = PEEP::getDispatcher()->getDispatchAttributes();
		if($attrs['controller'] == 'BASE_CTRL_ComponentPanel' && $attrs['action'] == 'profile')
		{
			$username = $attrs['params']['username'];
			$user = BOL_UserService::getInstance()->findByUsername($username);
			$profileId = $user->id;
		}
		
		return $profileId;
	}
	
	public function getlastLikeId()
	{
		$sql = "SELECT `entityId` FROM ".$this->getNotificationsTables()." WHERE `pluginKey` = 'profilelike' ORDER BY `id` DESC LIMIT 1";
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
	
	public function addLike($userId, $profileId)
	{
		$this->dbo->query("INSERT INTO `" . $this->getTableName() . "` (`userId`, `profileId`) VALUES (:userId, :profileId)", array('userId' => $userId, 'profileId' => $profileId));

		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
		$name = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrls = BOL_UserService::getInstance()->getUserUrlsForList(array($userId));
		$entityId = $this->getlastLikeId();
		if(!$entityId)
		{
			$entityId = 1;
		}
		else
		{
			$entityId = $entityId[0]->entityId + 1;
		}
		$actor = array(
			'name' => BOL_UserService::getInstance()->getDisplayName($userId),
			'url' => BOL_UserService::getInstance()->getUserUrl($userId)
		);

        $params = array(
            'pluginKey' => 'profilelike',
            'entityType' => 'profilelike',
            'entityId' => $entityId,
			'action' => 'profilelike-notify_profilelike',
            'userId' => $profileId,
            'time' => time()
        );
        $data = array(
			'avatar' => $avatars[$userId],
            'string' => array(
                'key' => 'profilelike+notify_profilelike',
                'vars' => array(
					'actor' => $actor['name'],
					'actorUrl' => $actor['url'],
					'title' => $actor['name'],
					'url' => $userUrls[3]
                )
            ),
			'content' => 'profilelike+notify_profilelike'
        );

       
        $event = new PEEP_Event('notifications.add', $params, $data);
        PEEP::getEventManager()->trigger($event);
	}
	
	public function unLike($userId, $profileId)
	{
		$this->dbo->query("DELETE FROM `" . $this->getTableName() . "` WHERE userId = '".$userId."' AND `profileId` = '".$profileId."'");
		$avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
		$name = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrls = BOL_UserService::getInstance()->getUserUrlsForList(array($userId));
		$entityId = $this->getlastLikeId();
		if(!$entityId)
		{
			$entityId = 1;
		}
		else
		{
			$entityId = $entityId[0]->entityId + 1;
		}
		$actor = array(
			'name' => BOL_UserService::getInstance()->getDisplayName($userId),
			'url' => BOL_UserService::getInstance()->getUserUrl($userId)
		);

        $params = array(
            'pluginKey' => 'profilelike',
            'entityType' => 'profilelike',
            'entityId' => $entityId,
			'action' => 'profilelike-notify_unprofilelike',
            'userId' => $profileId,
            'time' => time()
        );
        $data = array(
			'avatar' => $avatars[$userId],
            'string' => array(
                'key' => 'profilelike+notify_unprofilelike',
                'vars' => array(
					'actor' => $actor['name'],
					'actorUrl' => $actor['url'],
					'title' => $actor['name'],
					'url' => $userUrls[3]
                )
            ),
			'content' => 'profilelike+notify_unprofilelike'
        );

        
         $event = new PEEP_Event('notifications.add', $params, $data);
        PEEP::getEventManager()->trigger($event);
	}
	
	public function checkLike($userId, $profileId)
	{
		$sql = "SELECT * FROM ".$this->getTableName()." 
				WHERE `userId` = '".$userId."' AND
				profileId = '".$profileId."'
				LIMIT 1";
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
	
	public function whoLikeYou($userId, $limit)
	{
		$sql = "SELECT a.userId, b.*
				FROM ".$this->getTableName()." a
				LEFT JOIN ".BOL_UserDao::getInstance()->getTableName()." b on b.id = a.userId
				WHERE `profileId` = '".$userId."'
				ORDER BY a.id DESC LIMIT ".$limit." ";
				
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
	
	public function peopleWhoProfilelike($userId)
	{
		$sql = "SELECT a.userId, b.*
				FROM ".$this->getTableName()." a
				LEFT JOIN ".BOL_UserDao::getInstance()->getTableName()." b on b.id = a.userId
				WHERE `profileId` = '".$userId."' ";
	
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
	
	public function mostLikeMembers($limit)
	{
		$sql = "SELECT COUNT(a.profileId) AS cnt, b.*
				FROM ".$this->getTableName()." a
				LEFT JOIN ".BOL_UserDao::getInstance()->getTableName()." b ON b.id = a.profileId
				WHERE b.id IS NOT NULL
				GROUP BY profileId
				ORDER BY cnt DESC LIMIT ".$limit."";
				
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
	
	public function mostLikeMembersCtr()
	{
		$sql = "SELECT COUNT(a.profileId) AS cnt, b.*
				FROM ".$this->getTableName()." a
				LEFT JOIN ".BOL_UserDao::getInstance()->getTableName()." b ON b.id = a.profileId
				WHERE b.id IS NOT NULL
				GROUP BY profileId
				ORDER BY cnt DESC";
				
		return $this->dbo->queryForObjectList($sql, PROFILELIKE_BOL_ProfilelikeDao::getInstance()->getDtoClassName(), array());
	}
}
