<?php

class PHOTO_CMP_UserPhotoAlbumsWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        $userId = $paramObj->additionalParamList['entityId'];

        $user = BOL_UserService::getInstance()->getUserName($userId);
        $this->assign('user', $user);

        $num = isset($paramObj->customParamList['albumsCount']) ? $paramObj->customParamList['albumsCount'] : 4;

        $albums = $photoAlbumService->findUserAlbumList($userId, 1, $num);
        
        if ( $albums )
        {
            $event = PEEP::getEventManager()->trigger(
                new PEEP_Event('photo.albumsWidgetReady', array(), $albums)
            );
            $this->assign('albums', $event->getData());

            $albumsCount = $photoAlbumService->countUserAlbums($userId);

            $this->assign('albumsCount', $albumsCount);
        }
        else
        {
            if ( !$paramObj->customizeMode )
            {
                $this->setVisible(false);
            }

            $this->assign('albums', null);
            $this->assign('albumsCount', 0);
            $albumsCount = 0;
        }
        
        // privacy check
        $viewerId = PEEP::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = PEEP::getUser()->isAuthorized('photo');
        
        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => 'photo_view_album', 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new PEEP_Event('privacy_check_permission', $privacyParams);
            
            try {
                PEEP::getEventManager()->trigger($event);
            }
            catch ( RedirectException $e )
            {
                $this->setVisible(false);
            }
        }

        $showTitles = isset($paramObj->customParamList['showTitles']) ? $paramObj->customParamList['showTitles'] : false;
        $this->assign('showTitles', $showTitles);

        $lang = PEEP::getLanguage();

        $this->setSettingValue(self::SETTING_TOOLBAR, array(
            array('label' => $lang->text('photo', 'total_albums', array('total' => $albumsCount))),
            array('label' => $lang->text('base', 'view_all'), 'href' => PEEP::getRouter()->urlForRoute('photo_user_albums', array('user' => $user)))
        ));
    }

    public static function getSettingList()
    {
        $lang = PEEP::getLanguage();

        $settingList = array();
        $settingList['albumsCount'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $lang->text('photo', 'cmp_widget_photo_albums_count'),
            'optionList' => array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10),
            'value' => 3
        );

        $settingList['showTitles'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => $lang->text('photo', 'cmp_widget_photo_albums_show_titles'),
            'value' => true
        );

        return $settingList;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('photo', 'user_photo_albums_widget'),
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}
