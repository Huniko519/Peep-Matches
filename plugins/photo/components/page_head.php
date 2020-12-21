<?php

class PHOTO_CMP_PageHead extends PEEP_Component
{
    public function __construct( $ownerMode, $album )
    {
        parent::__construct();

        $language = PEEP::getLanguage();

        $isAuthorized = PEEP::getUser()->isAuthorized('photo', 'upload');
        $this->assign('isAuthorized', $isAuthorized);

        if ( $isAuthorized )
        {
            $language->addKeyForJs('photo', 'album_name');
            $language->addKeyForJs('photo', 'album_desc');
            $language->addKeyForJs('photo', 'create_album');
            $language->addKeyForJs('photo', 'newsfeed_album');
            $language->addKeyForJs('photo', 'newsfeed_album_error_msg');
            $language->addKeyForJs('photo', 'upload_photos');
            $language->addKeyForJs('photo', 'close_alert');
        }
        else
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('photo', 'upload');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign('isPromo', true);
                $this->assign('promoMsg', json_encode($status['msg']));
            }
        }

        $this->assign('url', PEEP::getEventManager()->call(PHOTO_CLASS_EventHandler::EVENT_GET_ADDPHOTO_URL, array(
            'albumId' => (!empty($ownerMode) && !empty($album)) ? $album->id : 0
        )));
        
        $menu = new BASE_CMP_SortControl();
        $menu->setTemplate(PEEP::getPluginManager()->getPlugin('photo')->getCmpViewDir() . 'sort_control.html');

        $handler = PEEP::getRequestHandler()->getHandlerAttributes();

        if ( in_array($handler[PEEP_RequestHandler::ATTRS_KEY_ACTION], array('viewList', 'viewTaggedList')) )
        {
            $menu->addItem(
                'latest',
                $language->text('photo', 'menu_latest'),
                PEEP::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => 'latest'
                ))
            );

            if ( PHOTO_BOL_PhotoService::getInstance()->countPhotos('featured'))
            {
                $menu->addItem(
                    'featured',
                    $language->text('photo', 'menu_featured'),
                    PEEP::getRouter()->urlForRoute('view_photo_list', array(
                        'listType' => 'featured'
                    ))
                );
            }

            $menu->addItem(
                'toprated',
                $language->text('photo', 'menu_toprated'),
                PEEP::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => 'toprated'
                ))
            );

            $menu->addItem(
                'most_discussed',
                $language->text('photo', 'menu_most_discussed'),
                PEEP::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => 'most_discussed'
                ))
            );

            if ( $handler[PEEP_RequestHandler::ATTRS_KEY_ACTION] != 'viewTaggedList')
            {
                $menu->setActive(!empty($handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['listType']) ? $handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['listType'] : 'latest');
            }
            
           $menu->assign('initSearchEngine', TRUE);
        }
        else
        {
 
            if ( !$ownerMode )
            {
                $user = BOL_UserService::getInstance()->findByUsername($handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user']);
                $this->assign('user', $user);

                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id));
                $this->assign('avatar', $avatar[$user->id]);

                $onlineStatus = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($user->id));
                $this->assign('onlineStatus', $onlineStatus[$user->id]);
            }
            
            $menu->addItem(
                'userPhotos',
                $language->text('photo', 'menu_photos'),
                PEEP::getRouter()->urlForRoute('photo.user_photos', array(
                    'user' => $handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user']
                ))
            );

            $menu->addItem(
                'userAlbums',
                $language->text('photo', 'menu_albums'),
                PEEP::getRouter()->urlForRoute('photo_user_albums', array(
                    'user' => $handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user']
                ))
            );
            
            if ( in_array($handler[PEEP_RequestHandler::ATTRS_KEY_ACTION], array('userAlbums', 'userAlbum')) )
            {
                $menu->setActive('userAlbums');
            }
            else
            {
                $menu->setActive('userPhotos');
            }
        }

        $event = PEEP::getEventManager()->trigger(
            new BASE_CLASS_EventCollector(PHOTO_CLASS_EventHandler::EVENT_COLLECT_PHOTO_SUB_MENU)
        );
        
        foreach ( $event->getData() as $menuItem )
        {
            $menu->addItem(
                $menuItem['sortOrder'],
                $menuItem['label'],
                $menuItem['url'],
                isset($menuItem['isActive']) ? (bool) $menuItem['isActive'] : FALSE
            );
        }
        
        $this->addComponent('subMenu', $menu);
        
        if ( PEEP::getUser()->isAuthenticated() )
        {
            $userObj = PEEP::getUser()->getUserObject();
            
            if (
                in_array($handler[PEEP_RequestHandler::ATTRS_KEY_ACTION], array('viewList', 'viewTaggedList')) ||
                (!empty($handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user']) && $handler[PEEP_RequestHandler::ATTRS_KEY_VARLIST]['user'] == $userObj->username)
            )
            {

                $menuItems = array();

                $item = new BASE_MenuItem();
                $item->setKey('menu_explore');
                $item->setLabel($language->text('photo', 'menu_explore'));
                $item->setUrl(PEEP::getRouter()->urlForRoute('view_photo_list'));
                $item->setIconClass('peep_ic_lens');
                $item->setOrder(0);
                $item->setActive(in_array($handler[PEEP_RequestHandler::ATTRS_KEY_ACTION], array('viewList', 'viewTaggedList')));
                $menuItems[] = $item;

                $item = new BASE_MenuItem();
                $item->setKey('menu_my_photos');
                $item->setLabel($language->text('photo', 'menu_my_photos'));
                $item->setUrl(PEEP::getRouter()->urlForRoute('photo.user_photos', array('user' => $userObj->username)));
                $item->setIconClass('peep_ic_picture');
                $item->setOrder(1);
                $item->setActive($ownerMode);
                $menuItems[] = $item;

                $this->addComponent('photoMenu', new BASE_CMP_ContentMenu($menuItems));
            }

        }
    }
}
