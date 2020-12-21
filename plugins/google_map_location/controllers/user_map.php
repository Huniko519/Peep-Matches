<?php

class GOOGLELOCATION_CTRL_UserMap extends PEEP_ActionController
{
    const MAX_USERS_COUNT = 16;
    
    public function map()
    {
        $menu = BASE_CTRL_UserList::getMenu('map');
        $this->addComponent('menu', $menu);

        $language = PEEP::getLanguage();
        $this->setPageHeading($language->text('googlelocation', 'map_page_heading'));
        $this->setPageHeadingIconClass('peep_ic_bookmark');
        $this->assign("url_back",PEEP::getRouter()->urlForRoute("users"));

        $event = new PEEP_Event( 'googlelocation.get_map_component', array( 'userIdList' => 'all', 'backUri' => PEEP::getRouter()->getUri() ) );
        PEEP::getEventManager()->trigger($event);
        /* @var $map GOOGLELOCATION_CMP_Map */
        $map = $event->getData();
        $map->displaySearchInput(true);
        
        PEEP::getEventManager()->trigger(new PEEP_Event('googlelocation.add_js_lib'));
        
        $this->addComponent("map", $map);
    }

    private function getUserFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = '';

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid] =  $sexValue . ' ' . $age;
            }
        }

        return $fields;
    }
}
