<?php

class PRIVACY_CLASS_EventHandler
{

    public function __construct()
    {

    }

    public function addPrivacyPreferenceMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = PEEP_Router::getInstance();
        $language = PEEP::getLanguage();

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('privacy');
        $menuItem->setLabel($language->text('privacy', 'privacy_index'));
        $menuItem->setIconClass('peep_ic_lock');
        $menuItem->setUrl($router->urlForRoute('privacy_index'));
        $menuItem->setOrder(5);

        $event->add($menuItem);
    }

    

    public function addPrivacy( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();
        $params = $event->getParams();

        $event->add(array(
            'key' => 'everybody',
            'label' => $language->text('privacy', 'privacy_everybody'),
            'weight' => 0,
            'sortOrder' => 0
        ));

        $event->add(array(
            'key' => 'only_for_me',
            'label' => $language->text('privacy', 'privacy_only_for_me'),
            'weight' => 10,
            'sortOrder' => 100000
        ));
    }

    public function getActionPrivacy( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['ownerId']) || empty($params['action']) )
        {
            throw new InvalidArgumentException('Invalid parameters were provided!'); // TODO trow Exeption
        }

        return PRIVACY_BOL_ActionService::getInstance()->getActionValue($params['action'], $params['ownerId']);
    }

    public function getActionMainPrivacyByOwnerIdList( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['userIdList']) || !is_array($params['userIdList']) || empty($params['action']) )
        {
            throw new InvalidArgumentException('Invalid parameters were provided!'); // TODO trow Exeption
        }

        return PRIVACY_BOL_ActionService::getInstance()->getMainActionValue($params['action'], $params['userIdList']);
    }

    public function removeUserPrivacy( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['userId']) )
        {
            PRIVACY_BOL_ActionService::getInstance()->deleteActionDataByUserId((int) $params['userId']);
        }
    }

    public function removePluginPrivacy( PEEP_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['pluginKey']) )
        {
            PRIVACY_BOL_ActionService::getInstance()->deleteActionDataByPluginKey($params['pluginKey']);
        }
    }

    public function checkPremission( PEEP_Event $event )
    {

        $params = $event->getParams();

        $result = PRIVACY_BOL_ActionService::getInstance()->checkPermission($params);

        if ( $result['blocked'] )
        {
            $ownerId = (int) $params['ownerId'];

            $username = BOL_UserService::getInstance()->getUserName($ownerId);

            $exception = new RedirectException(PEEP::getRouter()->urlForRoute('privacy_no_permission', array('username' => $username)));

            $params['message'] = $result['message'];
            $params['privacy'] = $result['privacy'];

            PEEP::getSession()->set('privacyRedirectExceptionMessage', $params['message']);

            $exception->setData($params);

            throw $exception;
        }
    }

    public function checkPremissionForUserList( PEEP_Event $event )
    {
        $params = $event->getParams();

        $action = $params['action'];
        $ownerIdList = $params['ownerIdList'];
        $viewerId = $params['viewerId'];

        return PRIVACY_BOL_ActionService::getInstance()->checkPermissionForUserList($action, $ownerIdList, $viewerId);
    }

    public function permissionEverybody( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        if ( !empty($params['privacy']) && $params['privacy'] == 'everybody' )
        {
            if ( !empty($params['ownerId']) )
            {
                $privacy = array();
                $privacy = array(
                    'everybody' => array(
                        'blocked' => false
                    ));

                $event->add($privacy);
            }
        }

        if ( !empty($params['userPrivacyList']) && is_array($params['userPrivacyList']) )
        {
            $list = $params['userPrivacyList'];
            $resultList = array();

            foreach ( $list as $ownerId => $privacy )
            {
                if ( $privacy == 'everybody' )
                {
                    $privacy = array(
                        'privacy' => $privacy,
                        'blocked' => false,
                        'userId' => $ownerId
                    );
                    $event->add($privacy);
                }
            }
        }
    }

    public function permissionOnlyForMe( BASE_CLASS_EventCollector $event )
    {
        $language = PEEP::getLanguage();

        $params = $event->getParams();

        if ( !empty($params['privacy']) && $params['privacy'] == 'only_for_me' )
        {
            if ( !empty($params['ownerId']) )
            {
                $ownerId = (int) $params['ownerId'];
                $viewerId = (int) $params['viewerId'];

                $item = array();
                $item = array(
                    'only_for_me' => array(
                        'blocked' => true,
                    ));

                if ( $ownerId > 0 && $ownerId === $viewerId )
                {
                    $item = array(
                        'only_for_me' => array(
                            'blocked' => false
                        ));
                }

                $event->add($item);
            }
        }


        if ( !empty($params['userPrivacyList']) && is_array($params['userPrivacyList']) )
        {
            $list = $params['userPrivacyList'];

            $viewerId = (int) $params['viewerId'];

            $resultList = array();

            foreach ( $list as $ownerId => $privacy )
            {
                if ( $privacy == 'only_for_me' )
                {
                    $privacy = array(
                        'privacy' => $privacy,
                        'blocked' => true,
                        'userId' => $ownerId
                    );

                    if ( $ownerId > 0 && $ownerId === $viewerId )
                    {
                        $privacy = array(
                            'privacy' => $privacy,
                            'blocked' => false,
                            'userId' => $ownerId
                        );
                    }

                    $event->add($privacy);
                }
            }
        }
    }

    public function pluginIsActive()
    {
        return true;
    }

    public function genericInit()
    {
        PEEP::getEventManager()->bind('base.preference_menu_items', array($this, 'addPrivacyPreferenceMenuItem'));
        PEEP::getEventManager()->bind(PRIVACY_BOL_ActionService::EVENT_GET_PRIVACY_LIST, array($this, 'addPrivacy'));
        PEEP::getEventManager()->bind('plugin.privacy.get_privacy', array($this, 'getActionPrivacy'));
        PEEP::getEventManager()->bind('plugin.privacy.get_main_privacy', array($this, 'getActionMainPrivacyByOwnerIdList'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_USER_UNREGISTER, array($this, 'removeUserPrivacy'));
        PEEP::getEventManager()->bind(PEEP_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'removePluginPrivacy'));
        PEEP::getEventManager()->bind(PRIVACY_BOL_ActionService::EVENT_CHECK_PERMISSION, array($this, 'checkPremission'));
        PEEP::getEventManager()->bind(PRIVACY_BOL_ActionService::EVENT_CHECK_PERMISSION_FOR_USER_LIST, array($this, 'checkPremissionForUserList'));
        PEEP::getEventManager()->bind('plugin.privacy.check_permission', array($this, 'permissionEverybody'));
        PEEP::getEventManager()->bind('plugin.privacy.check_permission', array($this, 'permissionOnlyForMe'));
        PEEP::getEventManager()->bind('plugin.privacy', array($this, 'pluginIsActive'));
    }
	

    
}
