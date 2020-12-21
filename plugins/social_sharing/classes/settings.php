<?php


class SOCIALSHARING_CLASS_Settings
{
    protected static $sharedEntitiesList = array('facebook', 'twitter', 'googlePlus', 'pinterest', 'linkedin', 'digg', 'delicious', 'stumbleupon');
    protected static $placeList = array('base', 'egifts', 'events', 'stories', 'groups', 'links', 'photo', 'video', 'forum');

    public static function getEntityList()
    {
        return self::$sharedEntitiesList;
    }

    public static function getPlaseList()
    {
        $result = self::$placeList;

        foreach ( $result as $key => $item )
        {
            if( !PEEP::getPluginManager()->isPluginActive($item) )
            {
                unset($result[$key]);
            }
        }

        return $result;
    }
}