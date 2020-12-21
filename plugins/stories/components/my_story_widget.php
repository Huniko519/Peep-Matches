<?php

class STORIES_CMP_MyStoryWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        /* @var $service PostService */
        $service = PostService::getInstance();

        $userId = $paramObj->additionalParamList['entityId'];

        if ( $userId != PEEP::getUser()->getId() )
        {
            $this->isVisible(false);
            return;
        }

        $this->assign('count', (int) $service->countUserPost($userId));

        $this->assign('commentCount', $service->countUserPostComment($userId));

        $this->assign('draftCount', (int) $service->countUserDraft($userId));
    }

    public static function getSettingList()
    {
        $settingList = array();

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('stories', 'my_story'),
            self::SETTING_ICON => 'peep_ic_write',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}