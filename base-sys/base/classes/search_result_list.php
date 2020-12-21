<?php

class BASE_CLASS_SearchResultList extends BASE_CMP_Users
{
    public function getFields($userIdList)
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $q )
        {

            $fields[$uid] = array();

            $age = '';
            
            if(!empty($q['birthdate'])){

                $date = UTIL_DateTime::parseDate( $q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT );
                $dinfo = date_parse($q['birthdate']);
                
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }


            if ( !empty($q['sex']) )
            {
                $sex = $q['sex'];
                $sexValue = '';

                for( $i = 0 ; $i < 31; $i++ )
                {
                    $val = pow( 2, $i );
                    if ( (int)$sex & $val  )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                        'label' => '',
                        'value' => $sexValue . ' ' . $age
                    );
            }
        }

        return $fields;
    }
}