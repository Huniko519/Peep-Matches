<?php

abstract class GOOGLELOCATION_CMP_MapWidget extends BASE_CLASS_Widget
{    
    const MAX_USERS_COUNT = 2000;
    
    protected $map = null;
    protected $mapHeight = null;    
    protected $idList = array();   
    
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        $IdList = $this->assignList( $params );
        
        if ( empty($IdList) && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        $this->mapHeight = isset($params->customParamList['map_height']) ? (int) $params->customParamList['map_height'] : 350;
        $this->renderMap($IdList, $params);
        
        $this->setTemplate(PEEP::getPluginManager()->getPlugin('googlelocation')->getCmpViewDir().'map_widget.html');
    }
    
    abstract protected function assignList( BASE_CLASS_WidgetParameter $params );

    protected function getMap( BASE_CLASS_WidgetParameter $params )            
    {        
        return $map;
    }
    
    protected function renderMap($IdList, BASE_CLASS_WidgetParameter $params)
    {        
        $event = new PEEP_Event( 'googlelocation.get_map_component', array( 'userIdList' => $IdList, 'backUri' => PEEP::getRouter()->getUri() ) );
        PEEP::getEventManager()->trigger($event);
        /* @var $map GOOGLELOCATION_CMP_Map */
        $map = $event->getData();
        $map->setHeight($this->mapHeight . 'px');
        
        if ( !empty($params->customParamList['map_display_search']) )
        {
            $map->displaySearchInput(true);
        }
        
        PEEP::getEventManager()->trigger(new PEEP_Event('googlelocation.add_js_lib'));   

        $this->addComponent("map", $map);
    }
    
    public static function getSettingList()
    {
        $settingList = array();
        
        $settingList['map_height'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => PEEP_Language::getInstance()->text('googlelocation', 'widget_settings_map_height'),
            'value' => 350
        );
        
        $settingList['map_display_search'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => PEEP_Language::getInstance()->text('googlelocation', 'widget_settings_display_search'),
            'value' => false
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => PEEP_Language::getInstance()->text('googlelocation', 'widget_map_title'),
            self::SETTING_ICON => self::ICON_BOOKMARK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}
