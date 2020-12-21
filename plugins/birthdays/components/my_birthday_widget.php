<?php

class BIRTHDAYS_CMP_MyBirthdayWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = BIRTHDAYS_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findUserById($params->additionalParamList['entityId']);
        
        if( $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $eventParams =  array(
                'action' => 'birthdays_view_my_birthdays',
                'ownerId' => $user->getId(),
                'viewerId' => PEEP::getUser()->getId()
            );
        
        try
        {
            PEEP::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        $result = $service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d', strtotime('+7 day')), 0, 1, array( $user->getId()));
        $isComingSoon = !empty($result);
        $this->assign('ballonGreenSrc', PEEP::getPluginManager()->getPlugin('birthdays')->getStaticUrl().'img/' . 'birthday_cake.png');
        $data = BOL_QuestionService::getInstance()->getQuestionData(array( $user->getId() ), array('birthdate'));        

        if ( (!$isComingSoon && !$params->customizeMode) || !array_key_exists('birthdate', $data[$user->getId()]) )
        {
            $this->setVisible(false);
            return;
        }        
        
        $birtdate = $data[$user->getId()]['birthdate']; 
        $dateInfo = UTIL_DateTime::parseDate($birtdate, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
        $label = '';

        if ( $dateInfo['day'] == date('d') )
        {
            $label = '<span class="peep_green" style="font-weight: bold; font-size:15px; text-align:center; position:relative; bottom:70px;">' . PEEP::getLanguage()->text('base', 'date_time_today') . '</span> ';
        }
        else if ( $dateInfo['day'] == date('d') + 1 )
        {
            $label = '<span class="peep_green" style="font-weight: bold; font-size:15px; text-align:center; position:relative; bottom:70px;">' . PEEP::getLanguage()->text('base', 'date_time_tomorrow') . '</span> ';
        }
        else
        {
            $label = '<span class="peep_green" style="font-weight: bold; font-size:15px; text-align:center; position:relative; bottom:70px;">' . UTIL_DateTime::formatBirthdate($dateInfo['year'], $dateInfo['month'], $dateInfo['day']) . '</span>';
        }
        
        $this->assign('label', $label);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => PEEP::getLanguage()->text('birthdays', 'my_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}