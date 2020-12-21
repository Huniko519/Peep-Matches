<?php

class FRIENDS_CTRL_List extends PEEP_ActionController
{
    protected $params;

    /**
     * Get list of friendships
     *
     * @param array $params
     */
    public function index( $params )
    {
        if ( !PEEP::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
            return;
        }

        $this->params = $params;

        $language = PEEP::getLanguage();

        $userService = BOL_UserService::getInstance();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = (int) PEEP::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $listType = $params['list'];

        if ( $listType == 'user-friends' )
        {
            $this->setPageHeading(PEEP::getLanguage()->text('friends', 'user_friends_page_heading', array('user' => $params['user'])));
            $this->setPageTitle(PEEP::getLanguage()->text('friends', 'user_friends_page_title', array('user' => $params['user'])));
        }
        else
        {
            $this->setPageHeading(PEEP::getLanguage()->text('friends', 'my_friends_page_heading'));
            $this->setPageTitle(PEEP::getLanguage()->text('friends', 'my_friends_page_title'));
            $this->addComponent('menu', $this->getMenu());
        }

        $this->setPageHeadingIconClass('peep_ic_user');

        $this->assign('case', $listType);

        list($list, $itemCount) = $this->getInfo($first, $count, $listType);

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $rpp), 5));

        $idList = array();

        $userList = array();

        foreach ( $list as $dto )
        {
            $userList[] = array(
                'dto' => $dto
            );

            $idList[] = $dto->getId();
        }

        $questionList = array();
        $onlineInfo = array();
        $avatarArr = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';
$qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');
        if ($qLocation)
        
            if ( $qLocation->onView )
            
                $qs[] = 'googlemap_location';
            

        if ( !empty($idList) )
        {
            $avatarArr = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
            $questionList = BOL_QuestionService::getInstance()->getQuestionData($idList, $qs);

            if ( $listType != 'online' )
            {
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
                    if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
                    {
                        unset($onlineInfo[$userId]);
                    }
                }
            }
        }

        $this->assign('questionList', $questionList);
        $this->assign('avatars', $avatarArr);
        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('list', $userList);
    }

    /**
     * Get info about list of friends or friend requests
     *
     * @param integer $first
     * @param integer $count
     * @param string $listType
     * @return array( $userList, $count )
     */
    protected function getInfo( $first, $count, $listType )
    {
        $service = FRIENDS_BOL_Service::getInstance();
        $userService = BOL_UserService::getInstance();

        $userId = PEEP::getUser()->getId();

        switch ( $listType )
        {
            case 'friends':
                $idList = $service->findUserFriendsInList($userId, $first, $count);

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->countFriends($userId)
                );

            case 'sent-requests':

                $idList = $service->findFriendIdList($userId, $first, $count, 'sent-requests');

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->count($userId, null, FRIENDS_BOL_Service::STATUS_PENDING, FRIENDS_BOL_Service::STATUS_IGNORED)
                );

            case 'got-requests':

                $idList = $service->findFriendIdList($userId, $first, $count, 'got-requests');

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING)
                );

            case 'user-friends':

                $eventParams = array(
                    'action' => 'friends_view',
                    'ownerId' => $userId,
                    'viewerId' => PEEP::getUser()->getId()
                );

                PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);

                $user = BOL_UserService::getInstance()->findByUsername($this->params['user']);
                $userId = $user->getId();

                $idList = $service->findUserFriendsInList($userId, $first, $count);

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->countFriends($userId)
                );
        }

        return array(array(), 0);
    }

    /**
     * Get submenu for friendship lists
     *
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $items = array();
        $language = PEEP::getLanguage();
        $userId = PEEP::getUser()->getId();

        $count = FRIENDS_BOL_Service::getInstance()->countFriends($userId);
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'friends_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('friends');
        $item->setUrl(PEEP::getRouter()->urlForRoute('friends_list'));
        $item->setOrder(1);
        $item->setIconClass('peep_ic_clock');
        $items[] = $item;

        $count = FRIENDS_BOL_Service::getInstance()->count($userId, null, FRIENDS_BOL_Service::STATUS_PENDING, FRIENDS_BOL_Service::STATUS_IGNORED);
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'sent_requests_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('sent_requests');
        $item->setUrl(PEEP::getRouter()->urlForRoute('friends_lists', array('list' => 'sent-requests')));
        $item->setOrder(2);
        $item->setIconClass('peep_ic_push_pin');
        $items[] = $item;

        $count = FRIENDS_BOL_Service::getInstance()->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING);
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'got_requests_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('got_requests');
        $item->setUrl(PEEP::getRouter()->urlForRoute('friends_lists', array('list' => 'got-requests')));
        $item->setOrder(3);
        $item->setIconClass('peep_ic_push_pin');
        $items[] = $item;

        return new BASE_CMP_ContentMenu($items);
    }
}