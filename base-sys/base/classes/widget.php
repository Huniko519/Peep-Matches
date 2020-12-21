<?php

abstract class BASE_CLASS_Widget extends PEEP_Component
{
    const ACCESS_GUEST = 'guest';
    const ACCESS_MEMBER = 'member';
    const ACCESS_ALL = 'all';

    const SETTING_TITLE = 'title';
    const SETTING_WRAP_IN_BOX = 'wrap_in_box';
    const SETTING_SHOW_TITLE = 'show_title';
    const SETTING_ICON = 'icon';
    const SETTING_TOOLBAR = 'toolbar';
    const SETTING_CAP_CONTENT = 'capContent';
    const SETTING_FREEZE = 'freeze';
    const SETTING_AVALIABLE_SECTIONS = 'avaliable_sections';
    const SETTING_ACCESS_RESTRICTIONS = 'access_restrictions';
    const SETTING_RESTRICT_VIEW = 'restrict_view';

    const PRESENTATION_NUMBER = 'number';
    const PRESENTATION_TEXT = 'text';
    const PRESENTATION_TEXTAREA = 'textarea';
    const PRESENTATION_CHECKBOX = 'checkbox';
    const PRESENTATION_SELECT = 'select';
    const PRESENTATION_HIDDEN = 'hidden';
    const PRESENTATION_CUSTOM = 'custom';

    const ICON_ADD = "peep_ic_add";
    const ICON_ALOUD = "peep_ic_aloud";
    const ICON_APP = "peep_ic_app";
    const ICON_ATTACH = "peep_ic_attach";
    const ICON_BIRTHDAY = "peep_ic_birthday";
    const ICON_BOOKMARK = "peep_ic_bookmark";
    const ICON_CALENDAR = "peep_ic_calendar";
    const ICON_CART = "peep_ic_cart";
    const ICON_CHAT = "peep_ic_chat";
    const ICON_CLOCK = "peep_ic_clock";
    const ICON_COMMENT = "peep_ic_comment";
    const ICON_CUT = "peep_ic_cut";
    const ICON_DASHBOARD = "peep_ic_dashboard";
    const ICON_DELETE = "peep_ic_delete";
    const ICON_DOWN_ARROW = "peep_ic_down_arrow";
    const ICON_EDIT = "peep_ic_edit";
    const ICON_FEMALE = "peep_ic_female";
    const ICON_FILE = "peep_ic_file";
    const ICON_FILES = "peep_ic_files";
    const ICON_FLAG = "peep_ic_flag";
    const ICON_FOLDER = "peep_ic_folder";
    const ICON_FORUM = "peep_ic_forum";
    const ICON_FRIENDS = "peep_ic_friends";
    const ICON_GEAR_WHEEL = "peep_ic_gear_wheel";
    const ICON_HEART = "peep_ic_heart";
    const ICON_HELP = "peep_ic_help";
    const ICON_HOUSE = "peep_ic_house";
    const ICON_INFO = "peep_ic_info";
    const ICON_KEY = "peep_ic_key";
    const ICON_LEFT_ARROW = "peep_ic_left_arrow";
    const ICON_LENS = "peep_ic_lens";
    const ICON_LINK = "peep_ic_link";
    const ICON_LOCK = "peep_ic_lock";
    const ICON_MAIL = "peep_ic_mail";
    const ICON_MALE = "peep_ic_male";
    const ICON_MOBILE = "peep_ic_mobile";
    const ICON_MODERATOR = "peep_ic_moderator";
    const ICON_MONITOR = "peep_ic_monitor";
    const ICON_MOVE = "peep_ic_move";
    const ICON_MUSIC = "peep_ic_music";
    const ICON_NEW = "peep_ic_new";
    const ICON_OK = "peep_ic_ok";
    const ICON_ONLINE = "peep_ic_online";
    const ICON_PICTURE = "peep_ic_picture";
    const ICON_PLUGIN = "peep_ic_plugin";
    const ICON_PUSH_PIN = "peep_ic_push_pin";
    const ICON_REPLY = "peep_ic_reply";
    const ICON_RIGHT_ARROW = "peep_ic_right_arrow";
    const ICON_RSS = "peep_ic_rss";
    const ICON_SAVE = "peep_ic_save";
    const ICON_SCRIPT = "peep_ic_script";
    const ICON_SERVER = "peep_ic_server";
    const ICON_STAR = "peep_ic_star";
    const ICON_TAG = "peep_ic_tag";
    const ICON_TRASH = "peep_ic_trash";
    const ICON_UNLOCK = "peep_ic_unlock";
    const ICON_UP_ARROW = "peep_ic_up_arrow";
    const ICON_UPDATE = "peep_ic_update";
    const ICON_USER = "peep_ic_user";
    const ICON_VIDEO = "peep_ic_video";
    const ICON_WARNING = "peep_ic_warning";
    const ICON_WRITE = "peep_ic_write";


    private static $placeData = array();

    final public static function getPlaceData()
    {
        return self::$placeData;
    }

    final public static function setPlaceData( $placeData )
    {
        self::$placeData = $placeData;
    }



    public static function getSettingList()
    {
        return array();
    }

    public static function validateSettingList( $settingList )
    {

    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        if ( isset($settingList['title']) )
        {
            $settingList['title'] = UTIL_HtmlTag::stripJs($settingList['title']);
        }

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array();
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    private $runtimeSettings = array();

    public function setSettingValue( $setting, $value )
    {
        $this->runtimeSettings[$setting] = $value;
    }

    public function getRunTimeSettingList()
    {
        return $this->runtimeSettings;
    }
}

class WidgetSettingValidateException extends Exception
{
    private $fieldName;

    public function __construct( $message, $fieldName = null )
    {
        parent::__construct($message);

        $this->fieldName = trim($fieldName);
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }
}
