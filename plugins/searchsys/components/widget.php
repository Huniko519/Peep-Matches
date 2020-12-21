<?php

class SEARCHSYS_CMP_Widget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $this->setTemplate(PEEP::getPluginManager()->getPlugin('searchsys')->getCmpViewDir() . 'widget.html');
        
        if ( SEARCHSYS_BOL_Service::getInstance()->isPeepsys() )
        {
            $this->addComponent('cmp', new USEARCH_CMP_SearchSystem());
            $styles = 
                '.qsearch_user_search_cmp { text-align: center; }
                 .qsearch_user_search_cmp form .peep_googlelocation_search_miles_from { display: inline-block; width: 100%; padding-left: 1px; }
                 .qsearch_user_search_cmp .peep_qs_field { padding-bottom: 10px; }';
            PEEP::getDocument()->addStyleDeclaration($styles);
        }
        else 
        {
            $this->addComponent('cmp', new SEARCHSYS_CMP_UserSearch());
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('searchsys', 'widget_title'),
            self::SETTING_ICON => 'peep_ic_lens',
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}