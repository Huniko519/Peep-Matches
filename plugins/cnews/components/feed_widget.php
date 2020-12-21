<?php

abstract class CNEWS_CMP_FeedWidget extends BASE_CLASS_Widget
{
    private $feedParams = array();
    /**
     *
     * @var CNEWS_CMP_Feed
     */
    private $feed;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj, $template = null )
    {
        parent::__construct();

        $template = empty($template) ? 'feed_widget' : $template;
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('cnews')->getCmpViewDir() . $template . '.html');

        $this->feedParams['customizeMode'] = $paramObj->customizeMode;

        $this->feedParams['viewMore'] = $paramObj->customParamList['view_more'];
        $this->feedParams['displayCount'] = (int) $paramObj->customParamList['count'];

        $this->feedParams['displayCount'] = $this->feedParams['displayCount'] > 20
                ? 20
                : $this->feedParams['displayCount'];
    }

    public function setFeed( CNEWS_CMP_Feed $feed )
    {
        $this->feed = $feed;
    }

    public function onBeforeRender()
    {
        $this->feed->setup($this->feedParams);

        $this->addComponent('feed', $this->feed);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => PEEP::getLanguage()->text('cnews', 'widget_feed_title'),
            self::SETTING_WRAP_IN_BOX => false,
            
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getSettingList()
    {
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => PEEP::getLanguage()->text('cnews', 'widget_settings_count'),
            'optionList' => array(5 => '5', '10' => 10, '20' => 20),
            'value' => 10
        );

        $settingList['view_more'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => PEEP::getLanguage()->text('cnews', 'widget_settings_view_more'),
            'value' => true
        );

        return $settingList;
    }
}