<?php

class SPOTLIGHT_CMP_IndexWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $spotLightComponent = new SPOTLIGHT_CMP_Index($params->customParamList);

        $this->addComponent('spotLightComponent', $spotLightComponent);
    }

    public static function getStandardSettingValueList()
    {
        $list = array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('spotlight', 'userlist'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => 'peep_ic_heart'
        );

        return $list;
    }

    public static function getSettingList()
       {
       		$settingList['number_of_users'] = array(
               'presentation' => self::PRESENTATION_NUMBER,
               'label' => PEEP::getLanguage()->text('spotlight', 'cmp_widget_number_of_users'),
               'value' => 7,
           );

           return $settingList;
       }


    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}

