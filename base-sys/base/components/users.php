<?php

abstract class BASE_CMP_Users extends PEEP_Component
{
    protected $showOnline = true, $list = array();

    public function getContextMenu( $userId )
    {
        return null;
    }

    abstract public function getFields( $userIdList );

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline = true )
    {
        parent::__construct();

        $this->setTemplate(PEEP::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'users.html');

        $this->list = $list;
        $this->showOnline = $showOnline;

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $usersOnPage), 5));
    }

    protected function process( $list, $showOnline )
    {
        $service = BOL_UserService::getInstance();

        $idList = array();
        $userList = array();

        foreach ( $list as $dto )
        {
            $userList[] = array('dto' => $dto);
            $idList[] = $dto->getId();
        }

        $avatars = array();
        $usernameList = array();
        $displayNameList = array();
        $onlineInfo = array();
        $questionList = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);

            foreach ( $avatars as $userId => $avatarData )
            {
                $displayNameList[$userId] = isset($avatarData['title']) ? $avatarData['title'] : '';
            }
            $usernameList = $service->getUserNamesForList($idList);

            if ( $showOnline )
            {
                $onlineInfo = $service->findOnlineStatusForUserList($idList);
            }
        }

        $showPresenceList = array();

        $ownerIdList = array();

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            $ownerIdList[$userId] = $userId;
        }

        $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerIdList' => $ownerIdList,
                'viewerId' => PEEP::getUser()->getId()
            );

        $permissions = PEEP::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            // Check privacy permissions
            if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
            {
                $showPresenceList[$userId] = false;
                continue;
            }

            $showPresenceList[$userId] = true;
        }

        $contextMenuList = array();
        foreach ( $idList as $uid )
        {
            $contextMenu = $this->getContextMenu($uid);
            if ( $contextMenu )
            {
                $contextMenuList[$uid] = $contextMenu->render();
            }
            else
            {
                $contextMenuList[$uid] = null;
            }
        }

        $fields = array();

        $this->assign('contextMenuList', $contextMenuList);

        $this->assign('fields', $this->getFields($idList));
        $this->assign('questionList', $questionList);
        $this->assign('usernameList', $usernameList);
        $this->assign('avatars', $avatars);
        $this->assign('displayNameList', $displayNameList);
        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('showPresenceList', $showPresenceList);
        $this->assign('list', $userList);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->list, $this->showOnline);
    }
}