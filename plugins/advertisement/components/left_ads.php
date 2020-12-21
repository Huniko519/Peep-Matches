<?php

class ADS_CMP_LeftAds extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $cmp = new ADS_CMP_Ads(array('position' => 'left'));

        if ( !$cmp->isVisible() )
        {
            $this->setVisible(false);
        }
        else
        {
            $this->addComponent('ads', $cmp);
        }
    }

    public static function getSettingList()
    {
        return array();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_AVALIABLE_SECTIONS => array(BOL_ComponentService::SECTION_LEFT),
            self::SETTING_TITLE => PEEP::getLanguage()->text('ads', 'widget_panel_title'),
            self::SETTING_ICON => 'peep_ic_star'
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}