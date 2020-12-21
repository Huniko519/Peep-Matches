<?php

class PRIVACY_BOL_ActionService
{
    const EVENT_GET_PRIVACY_LIST = 'plugin.privacy.get_privacy_list';
    const EVENT_GET_ACTION_LIST = 'plugin.privacy.get_action_list';
    const EVENT_CHECK_PERMISSION = 'privacy_check_permission';
    const EVENT_CHECK_PERMISSION_FOR_USER_LIST = 'privacy_check_permission_for_user_list';

    const EVENT_ON_CHANGE_ACTION_PRIVACY = 'plugin.privacy.on_change_action_privacy';

    const DEFAULT_PRIVACY_WEIGHT = 5;

    /**
     * @var PRIVACY_BOL_ActionDataDao
     */
    private $actionDataDao;
    /**
     * @var array
     */
    private $actionList = array();
    /**
     * @var array
     */
    private $actionData = array();
    /**
     * @var array
     */
    private $cronDao = array();
    /**
     * Singleton instance.
     *
     * @var PRIVACY_BOL_ActionService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        /* @var $this->actionDataDao PRIVACY_BOL_ActionDataDao */
        $this->actionDataDao = PRIVACY_BOL_ActionDataDao::getInstance();
        $this->cronDao = PRIVACY_BOL_CronDao::getInstance();

        $event = new BASE_CLASS_EventCollector(PRIVACY_BOL_ActionService::EVENT_GET_ACTION_LIST);
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();

        $actionList = empty($data) ? array() : $data;

        $resultList = array();
        $lastActionOrder = 1000;

        foreach ( $actionList as $value )
        {
            if ( !empty($value['key']) && !empty($value['pluginKey']) )
            {
                $action = new PRIVACY_CLASS_Action();

                $action->key = $value['key'];
                $action->pluginKey = $value['pluginKey'];
                $action->defaultValue = !empty($value['defaultValue']) ? $value['defaultValue'] : null;
                $action->description = isset($value['description']) ? $value['description'] : null;
                $action->label = !empty($value['label']) ? $value['label'] : null;
                $action->sortOrder = !empty($value['sortOrder']) ? (int) $value['sortOrder'] : $lastActionOrder++;

                $resultList[$value['key']] = $action;
            }
        }

        $this->actionList = $resultList;
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PRIVACY_BOL_ActionService
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
     * @return array <PRIVACY_CLASS_Action>
     */
    public function findAllAction()
    {
        return $this->actionList;
    }

    /**
     *
     * @param string $key
     * @return PRIVACY_BOL_Action
     */
    public function findAction( $key )
    {
        return!empty($this->actionList[$key]) ? $this->actionList[$key] : null;
    }

    /**
     *
     * @param array $keyList
     * @return array <PRIVACY_BOL_Action>
     */
    public function findActionList( $keyList )
    {
        if ( empty($keyList) || !is_array($keyList) )
        {
            return array();
        }

        $resultList = array();

        foreach ( $this->actionList as $key => $value )
        {
            if ( in_array($key, $keyList) )
            {
                $resultList[$key] = $value;
            }
        }

        return $resultList;
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    public function deleteAction( $key )
    {
        $result = $this->actionDataDao->deleteByActionNamesList(array($key));
        return $result;
    }

 /**
     * @param string $actionKey
     * @param int $userId
     * @return array[userId][actionName]
  */
    public function getActionValue( $actionKey, $userId )
    {
        $result = $this->getActionValueListByUserIdList(array($actionKey), array($userId));
        return isset($result[$userId][$actionKey]) ? $result[$userId][$actionKey] : null;
    }

    /**
     * @param string $actionKey
     * @param array $ownerIdList
     * @return array[userId][actionName]
     */
    public function getMainActionValue( $actionKey, $ownerIdList )
    {
        if( empty($ownerIdList) || !array($ownerIdList) || empty($actionKey) )
        {
            return null;
        }

        $actionValuesEvent= new BASE_CLASS_EventCollector( PRIVACY_BOL_ActionService::EVENT_GET_PRIVACY_LIST );
        PEEP::getEventManager()->trigger($actionValuesEvent);
        $data = $actionValuesEvent->getData();

        $actionValuesInfo = empty($data) ? array() : $data;

        $privacyList = array();

        // -- sort action values
        foreach( $actionValuesInfo as $value )
        {
            $privacyList[$value['key']] = $value;

            if( !isset($privacyList[$value['key']]['weight']) )
            {
                $privacyList[$value['key']]['weight'] = self::DEFAULT_PRIVACY_WEIGHT;
            }

            $privacyList[$value['key']]['weight'] = (float) $privacyList[$value['key']]['weight'];
        }

        $weight = -9999999;
        $actionPrivacy = null;
        $result = $this->getActionValueListByUserIdList(array($actionKey), $ownerIdList);

        foreach( $ownerIdList as $userId )
        {
            $privacy = $result[$userId][$actionKey];

            if( $privacyList[$privacy]['weight'] > $weight )
            {
                $weight = $privacyList[$privacy]['weight'];
                $actionPrivacy = $privacy;
            }
        }

        return $privacy;
    }

    /**
     * @param array $actionList
     * @param int $userId
     * @return array[userId][actionName]
     */
    public function getActionValueList( array $actionList, $userId )
    {
        $result = $this->getActionValueListByUserIdList($actionList, array($userId));
        return $result[$userId];
    }

    /**
     * @param array $actionList
     * @param array $userIdList
     * @return array[userId][actionName]
     */
    public function getActionValueListByUserIdList( array $actionList, array $userIdList )
    {
        $resultList = array();

        foreach ( $userIdList as $userId )
        {
            $resultList[$userId] = array();
        }

        if ( $userIdList === null || !is_array($userIdList) || count($userIdList) === 0 )
        {
            return $resultList;
        }

        if ( $actionList === null || !is_array($actionList) || count($actionList) === 0 )
        {
            return $resultList;
        }

//        $usersBol = BOL_UserService::getInstance()->findUserListByIdList($userIdList);
        $usersBol = BOL_UserService::getInstance()->findUserIdListByIdList($userIdList);

        if ( $usersBol === null || count($usersBol) === 0 )
        {
            return $resultList;
        }

        $issetUserList = array();

        foreach ( $usersBol as $userId )
        {
            $issetUserList[$userId] = $userId;
        }

        $cachedActionList = array();
        $notCachedActionList = array();

        foreach ( $usersBol as $userId )
        {
            if ( !empty($this->actionData[$userId]) )
            {
                foreach ( $actionList as $key )
                {
                    if ( isset($this->actionData[$userId][$key]) && !isset($notCachedActionList[$key]) )
                    {
                        $cachedActionList[$key] = $key;
                    }
                    else
                    {
                        $notCachedActionList[$key] = $key;

                        if ( isset($cachedActionList[$key]) )
                        {
                            unset($cachedActionList[$key]);
                        }
                    }
                }
            }
            else
            {
                foreach ( $actionList as $key )
                {
                    $notCachedActionList[$key] = $key;
                }
                
                $cachedActionList = array();
            }
        }

        $actionDtoList = array();
        $actionData = array();

        if ( count($notCachedActionList) > 0 )
        {
            /* @var $this->actionDataDao PRIVACY_BOL_ActionDataDao */
            $actionDtoList = $this->findActionList($actionList);
            $actionData = $this->actionDataDao->findByActionListForUserList($notCachedActionList, $issetUserList);
        }
        
        foreach ( $userIdList as $userId )
        {
            foreach ( $actionDtoList as $dto )
            {
                $key = $dto->key;

                if ( isset($actionData[$userId][$key]) )
                {
                    $dataDto = $actionData[$userId][$key];

                    /* @var $dto PRIVACY_BOL_ActionData */
                    $this->actionData[$userId][$key] = $dataDto->value;
                    $resultList[$userId][$key] = $this->actionData[$userId][$key];
                }
                else
                {
                    $this->actionData[$userId][$key] = $dto->defaultValue;
                    $resultList[$userId][$key] = $this->actionData[$userId][$key];
                }
            }

            foreach ( $cachedActionList as $key )
            {
                $resultList[$userId][$key] = $this->actionData[$userId][$key];
            }
        }

        return $resultList;
    }

    /**
     * @param array $actionList <$key, value>
     * @param array $userId
     * @return boolean
     */
    public function saveActionValue( $actionKey, $value, $userId )
    {
        return $this->saveActionValues(array($actionKey => $value), $userId);
    }

    /**
     * @param array $actionList <$key, value>
     * @param array $userId
     * @return boolean
     */
    public function saveActionValues( array $actionList, $userId )
    {
        if ( $actionList === null || !is_array($actionList) || count($actionList) === 0 )
        {
            return false;
        }

        $userDto = BOL_UserService::getInstance()->findUserById($userId);

        if ( empty($userDto) )
        {
            return false;
        }

        $actionKeyList = array_keys($actionList);

        $actionDtoList = $this->findActionList($actionKeyList);

        $result = $this->actionDataDao->findByActionListForUserList($actionKeyList, array($userId));
        $actionDataDtoList = !empty($result[$userId]) ? $result[$userId] : array();

        $actionKeyList = array_keys($actionDtoList);
        $savedActionList = array();

        $affectedRows = 0;

        foreach ( $actionList as $key => $value )
        {
            if ( in_array($key, $actionKeyList) )
            {
                $actionDataDto = new PRIVACY_BOL_ActionData();

                if ( !empty($actionDataDtoList[$key]) )
                {
                    $actionDataDto = $actionDataDtoList[$key];
                }

                /* @var $action PRIVACY_CLASS_Action */
                $action = $actionDtoList[$key];

                $actionDataDto->key = $key;
                $actionDataDto->userId = $userId;
                $actionDataDto->pluginKey = $action->pluginKey;
                $actionDataDto->value = $value;

                $this->actionDataDao->save($actionDataDto);

                if ( PEEP::getDbo()->getAffectedRows() )
                {
                    $affectedRows++;

                    $cronDto = new PRIVACY_BOL_Cron();
                    $cronDto->userId = $userId;
                    $cronDto->action = $key;
                    $cronDto->value = $value;
                    $cronDto->timeStamp = time();

                    $savedActionList[] = $cronDto;
                }

                if ( isset($this->actionData[$userId][$key]) )
                {
                    unset($this->actionData[$userId][$key]);
                }
            }
        }
        
        if ( $affectedRows )
        {
            PRIVACY_BOL_CronDao::getInstance()->batchSaveOrUpdate($savedActionList);
        }
        
        return $affectedRows;
    }

    public function deleteActionDataByUserId( $userId )
    {
        return $this->actionDataDao->deleteByUserId($userId);
    }

    public function deleteActionDataByPluginKey( $pluginKey )
    {
        return $this->actionDataDao->deleteByPluginKey($pluginKey);
    }

    /**
    * @param array $params
    * @param array &$result
    * @return array
    */
    
    public function checkPermissionForUserList( $action, $ownerIdList, $viewerId )
    {
        $resultList = array();

        $ignoreBlock = false;

        if ( PEEP::getAuthorization()->isUserAuthorized($viewerId, BOL_AuthorizationService::ADMIN_GROUP_NAME ) )
        {
            $ignoreBlock = true;
        }

        $actionDto = $this->findAction($action);

        if ( !empty($actionDto) && PEEP::getAuthorization()->isUserAuthorized($viewerId, $actionDto->pluginKey) )
        {
            $ignoreBlock = true;
        }

        $viewerId = (int) $viewerId;

        $userPrivacyList = $this->getActionValueListByUserIdList( array($action), $ownerIdList );
        $userPrivacy = array();
        foreach ( $userPrivacyList as $ownerId => $actionList )
        {
            $result = array();
            $result['blocked'] = false;

            if ( !empty($actionList[$action]) )
            {
				$result['privacy'] = $actionList[$action];
				$userPrivacy[$ownerId] = $actionList[$action];
			}

            $resultList[$ownerId] = $result;

        }
        
        if ( !$ignoreBlock )
        {
            $result['blocked'] = true;

            $eventParams = array(
                'action' => $action,
                'userPrivacyList' => $userPrivacy, // array( userId => privacy )
                'viewerId' => $viewerId
            );

            $event = new BASE_CLASS_EventCollector('plugin.privacy.check_permission', $eventParams);

            PEEP::getEventManager()->getInstance()->trigger($event);

            $dataList = $event->getData();

            foreach( $dataList as $data  )
            {
                if ( empty($data) )
                {
                    continue;
                }

                $ownerId = $data['userId'];

                if ( $userPrivacy[$ownerId] == $data['privacy'] )
                {
                    $resultList[$ownerId]['blocked'] = $data['blocked'];
                }
            }
        }
        
        return $resultList;
    }

    /**
     * @param array $params
     * @param array &$result
     * @return array
     */
    public function checkPermission( $params )
    {
        if ( !isset($params['ownerId']) )
        {
            throw new InvalidArgumentException('Invalid parameter ownerId!');
        }

        $action = $params['action'];
        $ownerId = (int)$params['ownerId'];
        $viewerId = (int)$params['viewerId'];

        $user = BOL_UserService::getInstance()->findUserById($ownerId);

        if( $user === null )
        {
            // do not block content if user has been removed
            return array( 'blocked' => false );
            
            // TODO: throw exception
            // throw new InvalidArgumentException('Invalid parameter ownerId!');
        }

        $result = array();

        $langParams = array(
            'username' => $user->username,
            'display_name' => BOL_UserService::getInstance()->getDisplayName($ownerId)
        );

        $result['blocked'] = true;
        $result['message'] = PEEP::getLanguage()->getInstance()->text('privacy', 'privacy_no_permission_message', $langParams);

        $eventParams = array(
            'action' => $action,
            'ownerId' => $ownerId
        );

        $privacy = PEEP::getEventManager()->getInstance()->call('plugin.privacy.get_privacy', $eventParams);

        $result['privacy'] = $privacy;

        $eventParams = array(
            'action' => $action,
            'privacy' => $privacy,
            'ownerId' => $ownerId,
            'viewerId' => $viewerId
        );

        $event = new BASE_CLASS_EventCollector('plugin.privacy.check_permission', $eventParams);

        PEEP::getEventManager()->getInstance()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $data = call_user_func_array('array_merge', $event->getData());
        }
        
        if ( isset($data[$privacy]) )
        {
            $result['blocked'] = false;

            if ( $data[$privacy]['blocked'] )
            {
                $result['blocked'] = true;
                
                if ( !empty($data[$privacy]['message']) )
                {
                    $result['message'] = $data[$privacy]['message'];
                }
            }
        }

        if ( PEEP::getAuthorization()->isUserAuthorized($viewerId, BOL_AuthorizationService::ADMIN_GROUP_NAME ) )
        {
            $result['blocked'] = false;
            return $result;
        }

        $actionDto = $this->findAction($action);
        
        if ( !empty($actionDto) && PEEP::getAuthorization()->isUserAuthorized($viewerId, $actionDto->pluginKey) )
        {
            $result['blocked'] = false;
        }

        return $result;
    }

    public function cronUpdatePrivacy()
    {
        $objectList = $this->cronDao->getUpdatedActions();

        $idList = array();

        $userActionList = array();

        foreach( $objectList as $object )
        {
            $idList[] = $object->id;
            $userActionList[$object->userId][$object->action] = $object->value;
        }
        
        $this->cronDao->setProcessStatus($idList);

        foreach ( $userActionList as $userId => $actions )
        {
            $params = array(
                'userId' => $userId,
                'actionList' => $actions
            );
            
            $event = new PEEP_Event(self::EVENT_ON_CHANGE_ACTION_PRIVACY, $params);
            PEEP::getEventManager()->trigger($event);
        }

        $this->cronDao->deleteByIdList($idList);
    }
}
