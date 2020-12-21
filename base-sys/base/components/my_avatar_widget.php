<?php

class BASE_CMP_MyAvatarWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $avatarService = BOL_AvatarService::getInstance();
        $userId = PEEP::getUser()->getId();

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $this->assign('avatar', $avatars[$userId]);

        $event = new BASE_CLASS_EventCollector('base.on_avatar_toolbar_collect', array(
            'userId' => $userId
        ));

        PEEP::getEventManager()->trigger($event);

        $toolbarItems = $event->getData();
        $tplToolbarItems = array();
        foreach ( $toolbarItems as $item )
        {
            if ( empty($item['title']) || empty($item['url']) || empty($item['iconClass']) )
            {
                continue;
            }

            $order = empty($item['order']) ? count($tplToolbarItems) + 1 : (int) $item['order'];

            if ( !empty($tplToolbarItems[$order]) )
            {
                $order = count($tplToolbarItems) + 1;
            }

            $tplToolbarItems[$order] = $item;
        }

        ksort($tplToolbarItems);

        $this->assign('toolbarItems', array_values($tplToolbarItems));
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_AVALIABLE_SECTIONS => array(BOL_ComponentService::SECTION_SIDEBAR),
            self::SETTING_TITLE => PEEP::getLanguage()->text('base', 'my_avatar_widget'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => 'peep_ic_user'
        );
    }
}