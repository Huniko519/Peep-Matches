<?php

class SEARCHSYS_CMP_DashboardWidget extends SEARCHSYS_CMP_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct($params);
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}