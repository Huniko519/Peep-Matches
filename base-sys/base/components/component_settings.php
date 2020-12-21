<?php

class BASE_CMP_ComponentSettings extends PEEP_Component
{
    /**
     * Component default settings
     *
     * @var array
     */
    private $defaultSettingList = array();
    /**
     * Component default settings
     *
     * @var array
     */
    private $componentSettingList = array();
    private $standardSettingValueList = array();
    private $hiddenFieldList = array();
    private $access;

    private $uniqName;

    /**
     * Class constructor
     *
     * @param array $menuItems
     */
    public function __construct( $uniqName, array $componentSettings = array(), array $defaultSettings = array(), $access = null )
    {
        parent::__construct();

        $this->componentSettingList = $componentSettings;
        $this->defaultSettingList = $defaultSettings;
        $this->uniqName = $uniqName;
        $this->access = $access;
    }

    public function setStandardSettingValueList( $valueList )
    {
        $this->standardSettingValueList = $valueList;
    }

    protected function makeSettingList( $defaultSettingList )
    {
        $settingValues = $this->standardSettingValueList;
        foreach ( $defaultSettingList as $name => $value )
        {
            $settingValues[$name] = $value;
        }

        return $settingValues;
    }

    public function markAsHidden( $settingName )
    {
        $this->hiddenFieldList[] = $settingName;
    }

    /**
     * @see PEEP_Renderable::onBeforeRender()
     *
     */
    public function onBeforeRender()
    {
        $settingValues = $this->makeSettingList($this->defaultSettingList);

        $this->assign('values', $settingValues);

        $this->assign('avaliableIcons', IconCollection::allWithLabel());

        foreach ( $this->componentSettingList as $name => & $setting )
        {
            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_HIDDEN )
            {
                unset($this->componentSettingList[$name]);
                continue;
            }

            if ( isset($settingValues[$name]) )
            {
                $setting['value'] = $settingValues[$name];
            }

            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_CUSTOM )
            {
                $setting['markup'] = call_user_func($setting['render'], $this->uniqName, $name, empty($setting['value']) ? null : $setting['value']);
            }

            $setting['display'] = !empty($setting['display']) ? $setting['display'] : 'table';
        }

        $this->assign('settings', $this->componentSettingList);


        $authorizationService = BOL_AuthorizationService::getInstance();

        $roleList = array();
        $isModerator = PEEP::getUser()->isAuthorized('base');
        
        if ( $this->access == BASE_CLASS_Widget::ACCESS_GUEST || !$isModerator )
        {
            $this->markAsHidden(BASE_CLASS_Widget::SETTING_RESTRICT_VIEW);
        }
        else
        {
            $roleList = $authorizationService->findNonGuestRoleList();

            if ( $this->access == BASE_CLASS_Widget::ACCESS_ALL )
            {
                $guestRoleId = $authorizationService->getGuestRoleId();
                $guestRole = $authorizationService->getRoleById($guestRoleId);
                array_unshift($roleList, $guestRole);
            }
        }

        $this->assign('roleList', $roleList);

        $this->assign('hidden', $this->hiddenFieldList);
    }

}

class IconCollection
{
    private static $all = array(
        "peep_ic_add",
        "peep_ic_aloud",
        "peep_ic_app",
        "peep_ic_attach",
        "peep_ic_birthday",
        "peep_ic_bookmark",
        "peep_ic_calendar",
        "peep_ic_cart",
        "peep_ic_chat",
        "peep_ic_clock",
        "peep_ic_comment",
        "peep_ic_cut",
        "peep_ic_dashboard",
        "peep_ic_delete",
        "peep_ic_down_arrow",
        "peep_ic_edit",
        "peep_ic_female",
        "peep_ic_file",
        "peep_ic_files",
        "peep_ic_flag",
        "peep_ic_folder",
        "peep_ic_forum",
        "peep_ic_friends",
        "peep_ic_gear_wheel",
        "peep_ic_help",
        "peep_ic_heart",
        "peep_ic_house",
        "peep_ic_info",
        "peep_ic_key",
        "peep_ic_left_arrow",
        "peep_ic_lens",
        "peep_ic_link",
        "peep_ic_lock",
        "peep_ic_mail",
        "peep_ic_male",
        "peep_ic_mobile",
        "peep_ic_moderator",
        "peep_ic_monitor",
        "peep_ic_move",
        "peep_ic_music",
        "peep_ic_new",
        "peep_ic_ok",
        "peep_ic_online",
        "peep_ic_picture",
        "peep_ic_plugin",
        "peep_ic_push_pin",
        "peep_ic_reply",
        "peep_ic_right_arrow",
        "peep_ic_rss",
        "peep_ic_save",
        "peep_ic_script",
        "peep_ic_server",
        "peep_ic_star",
        "peep_ic_tag",
        "peep_ic_trash",
        "peep_ic_unlock",
        "peep_ic_up_arrow",
        "peep_ic_update",
        "peep_ic_user",
        "peep_ic_video",
        "peep_ic_warning",
        "peep_ic_write"
    );

    public static function all()
    {
        return self::$all;
    }

    public static function allWithLabel()
    {
        $out = array();

        foreach ( self::$all as $icon )
        {
            $item = array();
            $item['class'] = $icon;
            $item['label'] = ucfirst(str_replace('_', ' ', substr($icon, 6)));
            $out[] = $item;
        }

        return $out;
    }
}
