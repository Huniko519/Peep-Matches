<?php

class BASE_CMP_AddNewContent extends BASE_CLASS_Widget
{
    /**
     * @deprecated contstant
     */
    const REGISTRY_DATA_KEY = 'base_cmp_add_new_item';

    const EVENT_NAME = 'base.add_new_content_item';
    const DATA_KEY_ICON_CLASS = 'iconClass';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_ID = 'id';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME);
        PEEP::getEventManager()->trigger($event);
        $data = $event->getData();
        if( empty($data) )
        {
            $this->setVisible(false);
            return;
        }
        $this->assign('items', $event->getData());
    }

    public static function getSettingList()
    {
        return array();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'component_add_new_box_cap_label'),
            self::SETTING_ICON => self::ICON_ADD
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}