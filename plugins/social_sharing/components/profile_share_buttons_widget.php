<?php

class SOCIALSHARING_CMP_ProfileShareButtonsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramsObj )
    {


        if ( !PEEP::getConfig()->getValue('socialsharing', 'api_key') )
        {
            $this->setVisible(false);
            return;
        }

        $userId = $paramsObj->additionalParamList['entityId'];
        $user = BOL_UserService::getInstance()->findUserById($userId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId);

        $title  = BOL_UserService::getInstance()->getDisplayName($userId);

        $cmp = new SOCIALSHARING_CMP_ShareButtons();
        $cmp->setTitle($title);
        $cmp->setDescription(PEEP::getConfig()->getValue('base', 'site_description'));
        $cmp->setBoxClass('peep_social_sharing_widget');
        $cmp->setDisplayBlock(false);
        $cmp->setImageUrl($avatarUrl);
        
        $this->addComponent('buttons', $cmp);
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('socialsharing')->getCmpViewDir().'index_share_buttons_widget.html');
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