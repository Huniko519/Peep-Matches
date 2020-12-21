<?php

class BASE_CMP_ProfileWall extends BASE_CLASS_Widget
{

    /**
     * Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $userId = (int) $paramObj->additionalParamList['entityId'];

        $this->addComponent('comments', new BASE_CMP_Comments('base', 'profile', $userId, $userId, 2));
    }

    public static function getSettingList()
    {
        return array();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}