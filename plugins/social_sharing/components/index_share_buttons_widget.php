<?php

class SOCIALSHARING_CMP_IndexShareButtonsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {

        if ( !PEEP::getConfig()->getValue('socialsharing', 'api_key') || PEEP::getConfig()->getValue('base', 'guests_can_view') != 1 || PEEP::getConfig()->getValue('base', 'maintenance') )
        {
            $this->setVisible(false);
            return;
        }

        $cmp = new SOCIALSHARING_CMP_ShareButtons();
        $cmp->setTitle(PEEP::getConfig()->getValue('base', 'site_name'));
        $cmp->setDescription(PEEP::getConfig()->getValue('base', 'site_description'));
        $cmp->setBoxClass('peep_social_sharing_widget');
        $cmp->setDisplayBlock(false);
        $this->addComponent('buttons', $cmp);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => PEEP::getLanguage()->text('socialsharing', 'socialsharing_widget_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}