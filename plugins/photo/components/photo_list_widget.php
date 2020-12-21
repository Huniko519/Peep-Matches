<?php

class PHOTO_CMP_PhotoListWidget extends BASE_CLASS_Widget
{
    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $num = isset($paramObj->customParamList['photoCount']) ? $paramObj->customParamList['photoCount'] : 8;

        $cmpParams = array(
            'photoCount' => $num,
            'checkAuth' => false,
            'wrapBox' => false,
            'uniqId' => uniqid(),
            'showTitle' => false,
            'showToolbar' => true
        );

        if ( !$paramObj->customizeMode )
        {
            $items = array('latest', 'toprated');
            if ( $photoService->countPhotos('featured') )
            {
                $items[] = 'featured';
            }
            $menuItems = PHOTO_CMP_IndexPhotoList::getMenuItems($items, $cmpParams['uniqId']);
            $this->addComponent('menu', new BASE_CMP_WidgetMenu($menuItems));
        }
        else
        {
            $cmpParams['showMenu'] = false;
        }

        $cmp = new PHOTO_CMP_IndexPhotoList($cmpParams);
        $this->addComponent('cmp', $cmp);
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['photoCount'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => PEEP::getLanguage()->text('photo', 'cmp_widget_photo_count'),
            'value' => 8
        );

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        $validationMessage = PEEP::getLanguage()->text('photo', 'cmp_widget_photo_count_msg');

        if ( !preg_match('/^\d+$/', $settingList['photoCount']) )
        {
            throw new WidgetSettingValidateException($validationMessage, 'photoCount');
        }
        if ( $settingList['photoCount'] > 100 )
        {
            throw new WidgetSettingValidateException($validationMessage, 'photoCount');
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('photo', 'photo_list_widget'),
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_SHOW_TITLE => true
        );
    }
}
