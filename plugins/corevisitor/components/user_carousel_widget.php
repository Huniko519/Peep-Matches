<?php

class COREVISITOR_CMP_UserCarouselWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        
        $list = new COREVISITOR_CMP_UserCarousel(array(
            "list" => $paramObj->customParamList['list']
        ));
        
        $this->addComponent("list", $list);
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => PEEP::getLanguage()->text('corevisitor', 'ucarousel_widget_title'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getSettingList()
    {
        $language = PEEP::getLanguage();
        
        $settingList['list'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $language->text('corevisitor', 'ucarousel_settings_list'),
            'optionList' => array(
                "latest" => $language->text('corevisitor', 'ucarousel_settings_list_latest'),
                "online" => $language->text('corevisitor', 'ucarousel_settings_list_online'),
                "featured" => $language->text('corevisitor', 'ucarousel_settings_list_featured')
            ),
            'value' => "latest"
        );

        return $settingList;
    }
}