<?php

class BASE_CMP_CustomHtmlWidget extends BASE_CLASS_Widget
{
    private $content = false;
    private $nl2br = false;

    public function __construct( BASE_CLASS_WidgetParameter $paramObject )
    {
        parent::__construct();

        $params = $paramObject->customParamList;

        if ( !empty($params['content']) )
        {
            $this->content = $paramObject->customizeMode && !empty($_GET['disable-js']) ? UTIL_HtmlTag::stripJs($params['content']) : $params['content'];
        }

        if ( isset($params['nl_to_br']) )
        {
            $this->nl2br = (bool) $params['nl_to_br'];
        }
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_TEXTAREA,
            'label' => PEEP::getLanguage()->text('base', 'custom_html_widget_content_label'),
            'value' => ''
        );

        $settingList['nl_to_br'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => PEEP::getLanguage()->text('base', 'custom_html_widget_nl2br_label'),
            'value' => '0'
        );

        return $settingList;
    }

    public static function processSettingList( $settings, $place, $isAdmin )
    {
        if ( $place != BOL_ComponentService::PLACE_DASHBOARD && !PEEP::getUser()->isAdmin() )
        {
            $settings['content'] = UTIL_HtmlTag::stripJs($settings['content']);
            //$settings['content'] = UTIL_HtmlTag::stripTags($settings['content'], array('frame'), array(), true, true);
        }
        else
        {
            $settings['content'] = UTIL_HtmlTag::sanitize($settings['content']);
        }

       return parent::processSettingList($settings, $place, $isAdmin);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'custom_html_widget_default_title')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public function onBeforeRender()
    {
        $content = $this->nl2br ? nl2br( $this->content ) : $this->content;
        //$content = UTIL_HtmlTag::stripTags($this->content, array(), array(), (bool) $this->nl2br);
        $this->assign('content', $content);
    }
}