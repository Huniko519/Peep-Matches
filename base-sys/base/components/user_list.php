<?php

class BASE_CMP_UserList extends PEEP_Component
{
    /**
     * Default users count
     */
    const DEFAULT_USERS_COUNT = 10;

    /**
     * Count users
     * 
     * @var integer
     */
    protected $countUsers;

    /**
     * User list
     * 
     * @param array $params
     *      integer count
     *      string boxType
     */
    function __construct( array $params = array() )
    {
        parent::__construct();

        $this->countUsers = !empty($params['count']) 
            ? (int) $params['count'] 
            : self::DEFAULT_USERS_COUNT;

        $boxType = !empty($params['boxType']) 
            ? $params['boxType']
            : "";

        // init users short list
        $randId = UTIL_HtmlTag::generateAutoId('base_users_cmp');
        $data = $this->getData($this->countUsers);

        $menuItems = array();
        $dataToAssign = array();

        foreach ( $data as $key => $item )
        {
            $contId = "{$randId}_users_cmp_{$key}";
            $toolbarId = (!empty($item['toolbar']) ? "{$randId}_toolbar_{$key}" : false );

            $menuItems[$key] = array(
                'label' => $item['menu-label'],
                'id' => "{$randId}_users_cmp_menu_{$key}",
                'contId' => $contId,
                'active' => !empty($item['menu_active']),
                'toolbarId' => $toolbarId,
                        'display' => 1
            );

            $usersCmp = $this->getUsersCmp($item['userIds']);

            $dataToAssign[$key] = array(
                'users' => $usersCmp->render(),
                'active' => !empty($item['menu_active']),
                'toolbar' => (!empty($item['toolbar']) ? $item['toolbar'] : array() ),
                'toolbarId' => $toolbarId,
                'contId' => $contId
            );
        }

        $menu = $this->getMenuCmp($menuItems);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }

        // assign view variables
        $this->assign('widgetId', $randId);
        $this->assign('data', $dataToAssign);
        $this->assign('boxType', $boxType);
    }

    /**
     * Get data
     * 
     * @return array
     */
    public function getData()
    {
        $language = PEEP::getLanguage();

        $toolbar = array(
            'latest' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest'))
            ),
            'online' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'online'))
            ),
            'featured' => array(
                'label' => PEEP::getLanguage()->text('base', 'view_all'),
                'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured'))
            )
        );

        $userService = BOL_UserService::getInstance();
        $latestUsersCount = $userService->count();

        $latestUsersCount > $this->countUsers
            ? $this->assign('toolbar', array($toolbar['latest']))
            : $this->assign('toolbar', array());

        // fill array with result
        $resultList = array(
            'latest' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_latest'),
                'menu_active' => true,
                'userIds' => $this->getIdList($userService->findList(0, $this->countUsers)),
                'toolbar' => ( $latestUsersCount > $this->countUsers ? array($toolbar['latest']) : false ),
            ),
            'online' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_online'),
                'userIds' => $this->getIdList($userService->findOnlineList(0, $this->countUsers)),
                'toolbar' => ( $userService->countOnline() > $this->countUsers ? array($toolbar['online']) : false ),
            ));

        // get list of featured users
        $featuredIdLIst = $this->getIdList($userService->findFeaturedList(0, $this->countUsers));

        if ( !empty($featuredIdLIst) )
        {
            $resultList['featured'] = array(
                'menu-label' => $language->text('base', 'user_list_menu_item_featured'),
                'userIds' => $featuredIdLIst,
                'toolbar' => ( $userService->countFeatured() > $this->countUsers ? array($toolbar['featured']) : false ),
            );
        }

        $event = new PEEP_Event('base.userList.onToolbarReady', array(), $resultList);
        PEEP::getEventManager()->trigger($event);

        return  $event->getData();
    }

    /**
     * Get id list
     * 
     * @param array $users
     * @return array
     */
    protected function getIdList( array $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $user )
            {
                $resultArray[] = $user->getId();
            }
        }

        return $resultArray;
    }
    
    /**
     * Get users component
     * 
     * @param array $list
     * @return \BASE_CMP_AvatarUserList
     */
    protected  function getUsersCmp( array $list )
    {
        return new BASE_CMP_AvatarUserList($list);
    }

    /**
     * Get menu component
     * 
     * @param array $menuItems
     * @return \BASE_CMP_WidgetMenu
     */
    protected function getMenuCmp( array $menuItems )
    {
        return new BASE_CMP_WidgetMenu($menuItems);
    }
}