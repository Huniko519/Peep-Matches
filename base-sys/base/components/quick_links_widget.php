<?php

class BASE_CMP_QuickLinksWidget extends BASE_CLASS_Widget
{
    const EVENT_NAME = 'base.add_quick_link';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_COUNT = 'count';
    const DATA_KEY_COUNT_URL = 'count_url';
    const DATA_KEY_ACTIVE_COUNT = 'active_count';
    const DATA_KEY_ACTIVE_COUNT_URL = 'active_count_url';

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();
 
        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME);
        PEEP::getEventManager()->trigger($event);
        $items = $event->getData();
        $this->assign('data', $items);
        
        if( empty($items) )
        {
            $this->setVisible(false);
        }

    }
public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_HIDDEN,
            'label' => '',
            'value' => null
        );

        return $settingList;
    }
    
 

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'quick_links_cap_label'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO
        );
    }
}