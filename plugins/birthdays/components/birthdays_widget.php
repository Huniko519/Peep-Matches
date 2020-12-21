<?php

class BIRTHDAYS_CMP_BirthdaysWidget extends BASE_CMP_UsersWidget
{
    const SHOW_ONLY_TODAY = 'today';
    const SHOW_TODAY_AND_THIS_WEEK = 'this_week';

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        $this->forceDisplayMenu(true);

        $count = (int) $params->customParamList['count'];
        $displayType = trim($params->customParamList['show']);

        $language = PEEP::getLanguage();
        $service = BIRTHDAYS_BOL_Service::getInstance();

        $toolbar = array(
            'birthdays_today' => array(
                array(
                    'label' => PEEP::getLanguage()->text('base', 'view_all'),
                    'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays'))
                )
            ),
            'birthdays_this_week' => array(
                array(
                    'label' => PEEP::getLanguage()->text('base', 'view_all'),
                    'href' => PEEP::getRouter()->urlForRoute('base_user_lists', array('list' => 'birthdays'))
                )
            )
        );
        
        $dataArray = array();

        switch ( $displayType )
        {
            case self::SHOW_TODAY_AND_THIS_WEEK:

                $birthdaysCount = $service->countByBirthdayPeriod(date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+7 day')), null, array('everybody'));

                if ( $birthdaysCount > 0 )
                {
                    $dataArray = array(
                        'birthdays_this_week' => array(
                            'menu-label' => $language->text('birthdays', 'user_list_menu_item_birthdays_upcoming'),
                            'userIds' => array( 'key' => 'birthdays_this_week', 'list' => $this->getIdList($service->findListByBirthdayPeriod(date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+7 day')), 0, $count, null, array('everybody'))) ),
                            'toolbar' => ( $birthdaysCount > $count ? $toolbar['birthdays_this_week'] : false ),
                            'menu_active' => true
                        )
                    );

                    if ( $birthdaysCount > $count )
                    {
                        $this->setSettingValue(self::SETTING_TOOLBAR,$toolbar['birthdays_this_week']);
                    }
                }

            case self::SHOW_ONLY_TODAY:

                $todayBirthdaysCount = $service->countByBirthdayPeriod(date('Y-m-d'), date('Y-m-d'), null, array('everybody'));

                if ( $todayBirthdaysCount > 0 )
                {
                    $dataArray['birthdays_today'] = array(
                        'menu-label' => $language->text('birthdays', 'user_list_menu_item_birthdays_today'),
                        'userIds' => array( 'key' => 'birthdays_today', 'list' => $this->getIdList($service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d'), 0, $count, null, array('everybody'))) ),
                        'toolbar' => ( $todayBirthdaysCount > $count ?  $toolbar['birthdays_today'] : false ),
                        'menu_active' => true
                    );

                    if ( !empty($dataArray['birthdays_this_week']['menu_active']) )
                    {
                        $dataArray['birthdays_this_week']['menu_active'] = false;
                    }

                    $dataArray = array_reverse($dataArray);

                    if ( $todayBirthdaysCount > $count )
                    {
                        $this->setSettingValue(self::SETTING_TOOLBAR, $toolbar['birthdays_today']);
                    }
                }

                break;
        }

        if ( empty($dataArray) )
        {
            $this->setVisible(false);
        }

        return $dataArray;
    }

    //default settings
    public static function getSettingList()
    {
        $language = PEEP::getLanguage();

        $settingList = array(
            'count' => array(
                'presentation' => self::PRESENTATION_NUMBER,
                'label' => $language->text('birthdays', 'widget_setting_count_label'),
                'value' => '12'
            ),
            'show' => array(
                'presentation' => self::PRESENTATION_SELECT,
                'label' => $language->text('birthdays', 'widget_setting_show_label'),
                'optionList' => array(
                    self::SHOW_ONLY_TODAY => $language->text('birthdays', 'widget_setting_value_' . self::SHOW_ONLY_TODAY),
                    self::SHOW_TODAY_AND_THIS_WEEK => $language->text('birthdays', 'widget_setting_value_' . self::SHOW_TODAY_AND_THIS_WEEK)
                ),
                'value' => self::SHOW_ONLY_TODAY
            )
        );

        return $settingList;
    }

    // set title and toolbar
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('birthdays', 'widget_title'),
            self::SETTING_ICON => self::ICON_CALENDAR,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    // set who allowed to see widget
    public static function getAccess()
    {
        /*
          ACCESS_GUEST - for guests only,
          ACCESS_ALL  - everyone,
          ACCESS_MEMBER - only for registered users )
         */
        return self::ACCESS_ALL;
    }
    
    protected function getUsersCmp( $list )
    {
        $key = !empty($list['key']) ? $list['key'] : null;
        $idList = !empty($list['list']) ? $list['list'] : array();
        
        return new BIRTHDAYS_CMP_AvatarUserList($idList, $key);
    }
}