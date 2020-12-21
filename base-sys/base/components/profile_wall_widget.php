<?php

class BASE_CMP_ProfileWallWidget extends BASE_CLASS_Widget
{

    /**
     * Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $userId = (int) $paramObj->additionalParamList['entityId'];

        $params = $paramObj->customParamList;

        $commentParams = new BASE_CommentsParams('base', 'base_profile_wall');

        $commentParams->setEntityId($userId);

        if ( isset($params['comments_count']) )
        {
            $commentParams->setCommentCountOnPage($params['comments_count']);
        }

        if ( isset($params['display_mode']) )
        {
            $commentParams->setDisplayType($params['display_mode']);
        }

        $commentParams->setOwnerId($userId);
        $commentParams->setWrapInBox(false);

        $this->addComponent('comments', new BASE_CMP_Comments($commentParams));
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['comments_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => PEEP::getLanguage()->text('base', 'cmp_widget_wall_comments_count'),
            'optionList' => array('3' => 3, '5' => 5, '10' => 10, '20' => 20, '50' => 50),
            'value' => 10
        );

        $settingList['display_mode'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => PEEP::getLanguage()->text('base', 'cmp_widget_wall_comments_mode'),
            'optionList' => array(
                '1' => PEEP::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_1'),
                '2' => PEEP::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_2')
            ),
            'value' => 2
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'comments_widget_label'),
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}